<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy handles more specific authorization checks usually
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'exists:projects,id',
                // Custom check could go here or in Controller to ensure SDD is assigned
            ],
            'period_start' => ['required', 'date', 'date_format:Y-m-d'], // Usually Monday
            'period_end' => ['required', 'date', 'date_format:Y-m-d', 'after:period_start'], // Usually Sunday
            'status' => ['sometimes', Rule::in(['draft', 'submitted'])],
            'comments' => ['nullable', 'string'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.user_id' => ['required', 'exists:users,id'],
            'entries.*.hours_worked' => ['required', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
        ];
    }
}
