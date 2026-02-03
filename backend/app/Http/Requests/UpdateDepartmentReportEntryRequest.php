<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentReportEntryRequest extends FormRequest
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
            'project_id' => ['sometimes', 'nullable', 'integer', 'exists:projects,id'],
            'hours_worked' => ['sometimes', 'numeric', 'min:0', 'max:168'],
            'hours_allocated' => ['sometimes', 'numeric', 'min:0', 'max:168'],
            'tasks_completed' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:productive,underperforming,on_leave'],
            'work_description' => ['sometimes', 'string', 'max:5000'],
            'notes' => ['sometimes', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'hours_worked.min' => 'Hours worked cannot be negative.',
            'hours_worked.max' => 'Hours worked cannot exceed 168 hours per week.',
            'hours_allocated.min' => 'Hours allocated cannot be negative.',
            'hours_allocated.max' => 'Hours allocated cannot exceed 168 hours per week.',
        ];
    }
}
