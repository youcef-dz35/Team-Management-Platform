<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmendProjectReportRequest;
use App\Http\Requests\StoreProjectReportRequest;
use App\Http\Requests\UpdateProjectReportRequest;
use App\Models\ProjectReport;
use App\Models\ProjectReportEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ProjectReport::class);

        $user = Auth::user();

        $query = ProjectReport::with(['project', 'user']);

        // Scope filter for SDD is handled implicitly by Policy 'view' check not being applied per row here,
        // but we should actively filter the query for SDD users as list.
        if ($user->hasRole('sdd')) {
            $query->where('submitted_by', $user->id);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectReportRequest $request)
    {
        $this->authorize('create', ProjectReport::class);

        try {
            $report = DB::transaction(function () use ($request) {
                // Create Header
                $report = ProjectReport::create([
                    'project_id' => $request->project_id,
                    'submitted_by' => Auth::id(),
                    'reporting_period_start' => $request->reporting_period_start,
                    'reporting_period_end' => $request->reporting_period_end,
                    'status' => $request->status ?? 'draft',
                    'comments' => $request->comments,
                ]);

                // Create Entries if provided
                if ($request->has('entries')) {
                    foreach ($request->entries as $entryData) {
                        $report->entries()->create($entryData);
                    }
                }

                return $report;
            });

            return response()->json(['data' => $report->load('entries')], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectReport $projectReport)
    {
        $this->authorize('view', $projectReport);

        return response()->json(['data' => $projectReport->load(['project', 'entries.employee', 'amendments.user'])]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectReportRequest $request, ProjectReport $projectReport)
    {
        $this->authorize('update', $projectReport);

        try {
            $report = DB::transaction(function () use ($request, $projectReport) {
                // Update Header (including period dates for draft reports)
                $updateFields = $request->only([
                    'status',
                    'comments',
                    'reporting_period_start',
                    'reporting_period_end'
                ]);
                $projectReport->update(array_filter($updateFields, fn($v) => $v !== null));

                if ($request->has('entries')) {
                    // Full replace policy for draft entries
                    $projectReport->entries()->delete();
                    foreach ($request->entries as $entryData) {
                        $projectReport->entries()->create($entryData);
                    }
                }

                return $projectReport;
            });

            return response()->json(['data' => $report->fresh()->load('entries')]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit the report (Change status to submitted).
     */
    public function submit(Request $request, ProjectReport $projectReport)
    {
        // Check if already submitted
        if ($projectReport->status !== 'draft') {
            return response()->json(['message' => 'Report has already been submitted.'], 400);
        }

        $this->authorize('submit', $projectReport);

        $projectReport->update(['status' => 'submitted']);

        return response()->json(['data' => $projectReport->fresh(), 'status' => 'submitted']);
    }

    /**
     * Amend a submitted report.
     */
    public function amend(AmendProjectReportRequest $request, ProjectReport $projectReport)
    {
        $this->authorize('amend', $projectReport);

        return DB::transaction(function () use ($request, $projectReport) {
            // 1. Snapshot Old Data
            $oldData = $projectReport->load('entries')->toArray();

            // 2. Update fields if provided
            $updateData = ['status' => 'amended'];
            if ($request->has('comments')) {
                $updateData['comments'] = $request->comments;
            }
            if ($request->has('reporting_period_start')) {
                $updateData['reporting_period_start'] = $request->reporting_period_start;
            }
            if ($request->has('reporting_period_end')) {
                $updateData['reporting_period_end'] = $request->reporting_period_end;
            }
            $projectReport->update($updateData);

            // 3. Update Entries (Full Replace) if entries are provided
            if ($request->has('entries')) {
                $projectReport->entries()->delete();
                foreach ($request->entries as $entryData) {
                    $projectReport->entries()->create($entryData);
                }
            }

            // 4. Snapshot New Data
            $newData = $projectReport->fresh(['entries'])->toArray();

            // 5. Create Amendment Log
            $projectReport->amendments()->create([
                'user_id' => Auth::id(),
                'reason' => $request->amendment_reason,
                'old_data' => $oldData,
                'new_data' => $newData,
            ]);

            return response()->json(['data' => $projectReport->load(['entries', 'amendments'])], 200);
        });
    }

    /**
     * Remove the specified resource from storage.
     * NOTE: Per Constitution Principle II, reports cannot be deleted.
     */
    public function destroy(ProjectReport $projectReport)
    {
        // Reports cannot be deleted - return 405 Method Not Allowed
        return response()->json(['message' => 'Reports cannot be deleted. Use amend to correct errors.'], 405);
    }
}
