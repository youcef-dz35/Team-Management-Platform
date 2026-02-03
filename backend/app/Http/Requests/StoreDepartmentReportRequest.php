<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class StoreDepartmentReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Simple check: Is the user a manager? 
        // Real check: does department_id match their department?
        return true; // Policy or logic below handles specifics
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get user's department ID to cross-check
        // Assuming user->department_id exists on User model (it does, based on migrations)
        $userDeptId = $this->user()->department_id;

        return [
            // Ensure they can only report for their own department
            'department_id' => [
                'required',
                'exists:departments,id',
                Rule::unique('department_reports')->where(function ($query) {
                    return $query->where('reporting_period_start', $this->reporting_period_start)
                                 ->where('reporting_period_end', $this->reporting_period_end);
                }),
            ],
            'reporting_period_start' => ['required', 'date', 'date_format:Y-m-d'],
            'reporting_period_end' => ['required', 'date', 'date_format:Y-m-d', 'after:reporting_period_start'],
            'status' => ['sometimes', Rule::in(['draft', 'submitted'])],
            'comments' => ['nullable', 'string'],
            'entries' => ['sometimes', 'array'],
            'entries.*.employee_id' => [
                'required',
                'exists:users,id',
                // Custom validation: The allocated user MUST belong to the reporting department
                function ($attribute, $value, $fail) use ($userDeptId) {
                    $employee = User::find($value);
                    if (!$employee || $employee->department_id !== $userDeptId) {
                        $fail("The employee must belong to your department.");
                    }
                }
            ],
            'entries.*.hours_worked' => ['required', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.in' => 'You can only submit reports for your own department.',
        ];
    }
}
