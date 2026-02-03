<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmendDepartmentReportRequest;
use App\Http\Requests\StoreDepartmentReportRequest;
use App\Http\Requests\UpdateDepartmentReportRequest;
use App\Models\DepartmentReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DepartmentReport::class);

        $user = Auth::user();
        $query = DepartmentReport::with(['department', 'user']);

        // Department Managers see only their department's reports
        if ($user->hasRole('dept_manager')) {
            $query->where('department_id', $user->department_id);
        }
        // GM, Ops Manager, CEO, CFO see all (handled by policy)

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentReportRequest $request): JsonResponse
    {
        $this->authorize('create', DepartmentReport::class);

        try {
            return DB::transaction(function () use ($request) {
                $report = DepartmentReport::create([
                    'department_id' => $request->department_id,
                    'submitted_by' => Auth::id(),
                    'reporting_period_start' => $request->reporting_period_start,
                    'reporting_period_end' => $request->reporting_period_end,
                    'status' => $request->status ?? 'draft',
                    'comments' => $request->comments,
                ]);

                // Create entries if provided
                if ($request->has('entries')) {
                    foreach ($request->entries as $entry) {
                        $report->entries()->create($entry);
                    }
                }

                return response()->json(['data' => $report->load('entries')], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DepartmentReport $departmentReport): JsonResponse
    {
        $this->authorize('view', $departmentReport);

        return response()->json(['data' => $departmentReport->load(['department', 'entries.employee', 'user'])]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentReportRequest $request, DepartmentReport $departmentReport): JsonResponse
    {
        $this->authorize('update', $departmentReport);

        try {
            return DB::transaction(function () use ($request, $departmentReport) {
                // Update Header (including period dates for draft reports)
                $updateFields = $request->only([
                    'status',
                    'comments',
                    'reporting_period_start',
                    'reporting_period_end'
                ]);
                $departmentReport->update(array_filter($updateFields, fn($v) => $v !== null));

                if ($request->has('entries')) {
                    $departmentReport->entries()->delete();
                    foreach ($request->entries as $entry) {
                        $departmentReport->entries()->create($entry);
                    }
                }

                return response()->json(['data' => $departmentReport->fresh()->load('entries')]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit the report (Change status to submitted).
     */
    public function submit(Request $request, DepartmentReport $departmentReport): JsonResponse
    {
        // Check if already submitted
        if ($departmentReport->status !== 'draft') {
            return response()->json(['message' => 'Report has already been submitted.'], 400);
        }

        $this->authorize('submit', $departmentReport);

        $departmentReport->update(['status' => 'submitted']);

        return response()->json(['data' => $departmentReport->fresh(), 'status' => 'submitted']);
    }

    /**
     * Amend a submitted report.
     */
    public function amend(AmendDepartmentReportRequest $request, DepartmentReport $departmentReport): JsonResponse
    {
        $this->authorize('amend', $departmentReport);

        try {
            return DB::transaction(function () use ($request, $departmentReport) {
                // Snapshot old data
                $oldData = $departmentReport->load('entries')->toArray();

                // Update fields if provided
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
                $departmentReport->update($updateData);

                // Update entries if provided
                if ($request->has('entries')) {
                    $departmentReport->entries()->delete();
                    foreach ($request->entries as $entry) {
                        $departmentReport->entries()->create($entry);
                    }
                }

                // Snapshot new data
                $newData = $departmentReport->fresh(['entries'])->toArray();

                // Create amendment log
                $departmentReport->amendments()->create([
                    'amended_by' => Auth::id(),
                    'amendment_reason' => $request->amendment_reason,
                    'changes' => [
                        'old_data' => $oldData,
                        'new_data' => $newData,
                    ],
                ]);

                return response()->json(['data' => $departmentReport->load(['entries', 'amendments'])]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error amending report', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * NOTE: Per Constitution Principle II, reports cannot be deleted.
     */
    public function destroy(DepartmentReport $departmentReport): JsonResponse
    {
        // Reports cannot be deleted - return 405 Method Not Allowed
        return response()->json(['message' => 'Reports cannot be deleted. Use amend to correct errors.'], 405);
    }
}
