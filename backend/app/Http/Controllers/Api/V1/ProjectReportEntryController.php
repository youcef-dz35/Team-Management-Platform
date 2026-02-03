<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectReportEntryRequest;
use App\Http\Requests\UpdateProjectReportEntryRequest;
use App\Models\ProjectReport;
use App\Models\ProjectReportEntry;
use Illuminate\Http\JsonResponse;

/**
 * Project Report Entry Controller (Source A)
 *
 * Manages individual worker entries within a project report.
 * Each entry tracks hours worked and accomplishments for a specific worker.
 */
class ProjectReportEntryController extends Controller
{
    /**
     * Display a listing of entries for a project report.
     */
    public function index(ProjectReport $projectReport): JsonResponse
    {
        $this->authorize('view', $projectReport);

        $entries = $projectReport->entries()
            ->with('employee:id,name,email,employee_id')
            ->get();

        return response()->json([
            'data' => $entries,
        ]);
    }

    /**
     * Store a newly created entry in the project report.
     */
    public function store(StoreProjectReportEntryRequest $request, ProjectReport $projectReport): JsonResponse
    {
        $this->authorize('update', $projectReport);

        // Cannot add entries to submitted reports
        if ($projectReport->status !== 'draft') {
            return response()->json([
                'message' => 'Cannot add entries to a submitted report. Use amendment instead.',
            ], 400);
        }

        // Check if entry for this employee already exists
        $existingEntry = $projectReport->entries()
            ->where('user_id', $request->employee_id)
            ->first();

        if ($existingEntry) {
            return response()->json([
                'message' => 'An entry for this employee already exists in this report.',
                'existing_entry_id' => $existingEntry->id,
            ], 409);
        }

        $entry = $projectReport->entries()->create([
            'user_id' => $request->employee_id,
            'hours_worked' => $request->hours_worked,
            'tasks_completed' => $request->tasks_completed ?? 0,
            'status' => $request->status ?? 'on_track',
            'notes' => $request->accomplishments ?? $request->notes,
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
    public function show(ProjectReport $projectReport, ProjectReportEntry $entry): JsonResponse
    {
        $this->authorize('view', $projectReport);

        // Verify entry belongs to this report
        if ($entry->project_report_id !== $projectReport->id) {
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
    public function update(UpdateProjectReportEntryRequest $request, ProjectReport $projectReport, ProjectReportEntry $entry): JsonResponse
    {
        $this->authorize('update', $projectReport);

        // Verify entry belongs to this report
        if ($entry->project_report_id !== $projectReport->id) {
            return response()->json([
                'message' => 'Entry not found in this report.',
            ], 404);
        }

        // Cannot update entries in submitted reports
        if ($projectReport->status !== 'draft') {
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
    public function destroy(ProjectReport $projectReport, ProjectReportEntry $entry): JsonResponse
    {
        $this->authorize('update', $projectReport);

        // Verify entry belongs to this report
        if ($entry->project_report_id !== $projectReport->id) {
            return response()->json([
                'message' => 'Entry not found in this report.',
            ], 404);
        }

        // Cannot delete entries from submitted reports
        if ($projectReport->status !== 'draft') {
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
