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
            $query->where('user_id', $user->id);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectReportRequest $request)
    {
        $this->authorize('create', ProjectReport::class);

        return DB::transaction(function () use ($request) {
            // Create Header
            $report = ProjectReport::create([
                'project_id' => $request->project_id,
                'user_id' => Auth::id(),
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'status' => $request->status ?? 'draft',
                'comments' => $request->comments,
            ]);

            // Create Entries
            foreach ($request->entries as $entryData) {
                $report->entries()->create($entryData);
            }

            return response()->json($report->load('entries'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectReport $projectReport)
    {
        $this->authorize('view', $projectReport);

        return response()->json($projectReport->load(['project', 'entries.user', 'amendments.user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectReportRequest $request, ProjectReport $projectReport)
    {
        $this->authorize('update', $projectReport);

        return DB::transaction(function () use ($request, $projectReport) {
            // Update Header
            $projectReport->update($request->only(['status', 'comments']));

            if ($request->has('entries')) {
                // Full replace policy for draft entries
                $projectReport->entries()->delete();
                foreach ($request->entries as $entryData) {
                    $projectReport->entries()->create($entryData);
                }
            }

            return response()->json($projectReport->load('entries'));
        });
    }

    /**
     * Submit the report (Change status to submitted).
     */
    public function submit(Request $request, ProjectReport $projectReport)
    {
        $this->authorize('submit', $projectReport);

        $projectReport->update(['status' => 'submitted']);

        return response()->json(['message' => 'Report submitted successfully', 'report' => $projectReport]);
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

            // 2. Update Header (Comments/Status -> Remains submitted, but implicitly updated)
            if ($request->has('comments')) {
                $projectReport->update(['comments' => $request->comments]);
            }

            // 3. Update Entries (Full Replace)
            $projectReport->entries()->delete();
            foreach ($request->entries as $entryData) {
                $projectReport->entries()->create($entryData);
            }

            // 4. Snapshot New Data
            $newData = $projectReport->fresh(['entries'])->toArray();

            // 5. Create Amendment Log
            $projectReport->amendments()->create([
                'user_id' => Auth::id(),
                'reason' => $request->reason,
                'old_data' => $oldData,
                'new_data' => $newData,
            ]);

            return response()->json($projectReport->load(['entries', 'amendments']), 200);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectReport $projectReport)
    {
        $this->authorize('delete', $projectReport);

        $projectReport->delete();

        return response()->json(null, 204);
    }
}
