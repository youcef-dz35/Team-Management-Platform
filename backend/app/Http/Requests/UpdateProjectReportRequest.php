<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if report is already submitted - if so, deny update (must use amend)
        $report = $this->route('project_report');
        if ($report && $report->status === 'submitted') {
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Project and Period usually immutable after creation, but allowing update if draft
            'reporting_period_start' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'reporting_period_end' => ['sometimes', 'date', 'date_format:Y-m-d', 'after:reporting_period_start'],
            'status' => ['sometimes', Rule::in(['draft', 'submitted'])],
            'comments' => ['nullable', 'string'],
            'entries' => ['sometimes', 'array'],
            'entries.*.employee_id' => ['required_with:entries', 'exists:users,id'],
            'entries.*.hours_worked' => ['required_with:entries', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
        ];
    }
}
