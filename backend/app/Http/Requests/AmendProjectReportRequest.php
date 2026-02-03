<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmendProjectReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allowed for submitted reports
        $report = $this->route('project_report');
        if ($report && $report->status !== 'submitted') {
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
        // Amendment essentially replaces the entries data, but requires a reason
        return [
            'amendment_reason' => ['required', 'string', 'min:5'],
            'entries' => ['sometimes', 'array'],
            'entries.*.employee_id' => ['required_with:entries', 'exists:users,id'],
            'entries.*.hours_worked' => ['required_with:entries', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'], // Allow updating header comments too
            'reporting_period_start' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'reporting_period_end' => ['sometimes', 'date', 'date_format:Y-m-d'],
        ];
    }
}
