<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentReportEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:users,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'hours_worked' => ['required_without:hours_allocated', 'numeric', 'min:0', 'max:168'],
            'hours_allocated' => ['required_without:hours_worked', 'numeric', 'min:0', 'max:168'],
            'tasks_completed' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:productive,underperforming,on_leave'],
            'work_description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'], // Alias for work_description
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'hours_worked.min' => 'Hours worked cannot be negative.',
            'hours_worked.max' => 'Hours worked cannot exceed 168 hours per week.',
            'hours_allocated.min' => 'Hours allocated cannot be negative.',
            'hours_allocated.max' => 'Hours allocated cannot exceed 168 hours per week.',
        ];
    }
}
