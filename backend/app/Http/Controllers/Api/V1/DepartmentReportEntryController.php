<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentReportEntryRequest;
use App\Http\Requests\UpdateDepartmentReportEntryRequest;
use App\Models\DepartmentReport;
use App\Models\DepartmentReportEntry;
use Illuminate\Http\JsonResponse;

/**
 * Department Report Entry Controller (Source B)
 *
 * Manages individual employee entries within a department report.
 * Each entry tracks hours worked and work description for a specific employee.
 */
class DepartmentReportEntryController extends Controller
{
    /**
     * Display a listing of entries for a department report.
     */
    public function index(DepartmentReport $departmentReport): JsonResponse
    {
        $this->authorize('view', $departmentReport);

        $entries = $departmentReport->entries()
            ->with('employee:id,name,email,employee_id')
            ->get();

        return response()->json([
            'data' => $entries,
        ]);
    }

    /**
     * Store a newly created entry in the department report.
     */
    public function store(StoreDepartmentReportEntryRequest $request, DepartmentReport $departmentReport): JsonResponse
    {
        $this->authorize('update', $departmentReport);

        // Cannot add entries to submitted reports
        if ($departmentReport->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot add entries to a submitted report. Use amendment instead.',
            ], 400);
        }

        // Check if entry for this employee already exists
        $existingEntry = $departmentReport->entries()
            ->where('user_id', $request->employee_id)
            ->first();

        if ($existingEntry) {
            return response()->json([
                'message' => 'An entry for this employee already exists in this report.',
                'existing_entry_id' => $existingEntry->id,
            ], 409);
        }

        $entry = $departmentReport->entries()->create([
            'user_id' => $request->employee_id,
            'project_id' => $request->project_id,
            'hours_allocated' => $request->hours_worked ?? $request->hours_allocated,
            'tasks_completed' => $request->tasks_completed ?? 0,
            'status' => $request->status ?? 'productive',
            'notes' => $request->work_description ?? $request->notes,
        ]);

        $entry->load('employee:id,name,email,employee_id');

        return response()->json([
            'message' => 'Entry added successfully.',
            'data' => $entry,
        ], 201);
    }

    /**
     * Display the specified entry.
     */
    public function show(DepartmentReport $departmentReport, DepartmentReportEntry $entry): JsonResponse
    {
        $this->authorize('view', $departmentReport);

        // Verify entry belongs to this report
        if ($entry->department_report_id !== $departmentReport->id) {
            return response()->json([
                'message' => 'Entry not found in this report.',
            ], 404);
        }

        $entry->load('employee:id,name,email,employee_id');

        return response()->json([
            'data' => $entry,
        ]);
    }

    /**
     * Update the specified entry.
     */
    public function update(UpdateDepartmentReportEntryRequest $request, DepartmentReport $departmentReport, DepartmentReportEntry $entry): JsonResponse
    {
        $this->authorize('update', $departmentReport);

        // Verify entry belongs to this report
        if ($entry->department_report_id !== $departmentReport->id) {
            return response()->json([
                'message' => 'Entry not found in this report.',
            ], 404);
        }

        // Cannot update entries in submitted reports
        if ($departmentReport->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot update entries in a submitted report. Use amendment instead.',
            ], 400);
        }

        $entry->update($request->validated());
        $entry->load('employee:id,name,email,employee_id');

        return response()->json([
            'message' => 'Entry updated successfully.',
            'data' => $entry,
        ]);
    }

    /**
     * Remove the specified entry from the report.
     */
    public function destroy(DepartmentReport $departmentReport, DepartmentReportEntry $entry): JsonResponse
    {
        $this->authorize('update', $departmentReport);

        // Verify entry belongs to this report
        if ($entry->department_report_id !== $departmentReport->id) {
            return response()->json([
                'message' => 'Entry not found in this report.',
            ], 404);
        }

        // Cannot delete entries from submitted reports
        if ($departmentReport->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot delete entries from a submitted report.',
            ], 400);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Entry removed successfully.',
        ], 200);
    }
}
