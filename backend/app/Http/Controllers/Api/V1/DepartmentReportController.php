<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentReportRequest;
use App\Models\DepartmentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DepartmentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-filter by user's department if they are not a super-admin
        $user = Auth::user();
        $query = DepartmentReport::with(['department', 'user']);

        if (!$user->hasAnyRole(['ceo', 'cfo', 'gm'])) {
            $query->where('department_id', $user->department_id);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentReportRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $report = DepartmentReport::create([
                'department_id' => $request->department_id,
                'user_id' => Auth::id(),
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'status' => $request->status ?? 'draft',
                'comments' => $request->comments,
            ]);

            foreach ($request->entries as $entry) {
                $report->entries()->create($entry);
            }

            return response()->json($report->load('entries'), 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(DepartmentReport $departmentReport)
    {
        $this->authorize('view', $departmentReport);

        return response()->json($departmentReport->load(['entries.user', 'entries.project']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DepartmentReport $departmentReport)
    {
        $this->authorize('update', $departmentReport);

        // Similar logic to ProjectReport: Full replace of entries for drafts
        return DB::transaction(function () use ($request, $departmentReport) {
            $departmentReport->update($request->only(['comments', 'status']));

            if ($request->has('entries')) {
                $departmentReport->entries()->delete();
                foreach ($request->entries as $entry) {
                    $departmentReport->entries()->create($entry);
                }
            }

            return response()->json($departmentReport->load('entries'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DepartmentReport $departmentReport)
    {
        $this->authorize('delete', $departmentReport);
        $departmentReport->delete();
        return response()->json(null, 204);
    }
}
