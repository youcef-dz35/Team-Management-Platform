<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectReportEntryRequest extends FormRequest
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
            'hours_worked' => ['required', 'numeric', 'min:0', 'max:168'], // Max 168 hours per week
            'tasks_completed' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:on_track,at_risk,blocked'],
            'accomplishments' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'], // Alias for accomplishments
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
            'hours_worked.required' => 'Hours worked is required.',
            'hours_worked.min' => 'Hours worked cannot be negative.',
            'hours_worked.max' => 'Hours worked cannot exceed 168 hours per week.',
        ];
    }
}
