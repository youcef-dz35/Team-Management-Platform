<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $report = $this->route('department_report');
        return $report && $this->user()->can('update', $report);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comments' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'submitted'])],
            'reporting_period_start' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'reporting_period_end' => ['sometimes', 'date', 'date_format:Y-m-d', 'after:reporting_period_start'],
            'entries' => ['sometimes', 'array'],
            'entries.*.employee_id' => ['required_with:entries', 'exists:users,id'],
            'entries.*.hours_worked' => ['required_with:entries', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
        ];
    }
}