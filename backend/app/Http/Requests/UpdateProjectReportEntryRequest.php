<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectReportEntryRequest extends FormRequest
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
            'hours_worked' => ['sometimes', 'numeric', 'min:0', 'max:168'],
            'tasks_completed' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'string', 'in:on_track,at_risk,blocked'],
            'accomplishments' => ['sometimes', 'string', 'max:5000'],
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
        ];
    }
}
