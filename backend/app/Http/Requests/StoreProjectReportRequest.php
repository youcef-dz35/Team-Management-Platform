<?php

namespace App\Http\Requests;

use App\Rules\UserIsAssignedToProject;
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
                Rule::unique('project_reports')->where(function ($query) {
                    return $query->where('reporting_period_start', $this->reporting_period_start)
                                 ->where('reporting_period_end', $this->reporting_period_end);
                }),
                // Custom check could go here or in Controller to ensure SDD is assigned
            ],
            'reporting_period_start' => ['required', 'date', 'date_format:Y-m-d'], // Usually Monday
            'reporting_period_end' => ['required', 'date', 'date_format:Y-m-d', 'after:reporting_period_start'], // Usually Sunday
            'status' => ['sometimes', Rule::in(['draft', 'submitted'])],
            'comments' => ['nullable', 'string'],
            'entries' => ['sometimes', 'array'],
            'entries.*.employee_id' => ['required', 'exists:users,id', new UserIsAssignedToProject($this->project_id)],
            'entries.*.hours_worked' => ['required', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
        ];
    }
}
