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
            'reason' => ['required', 'string', 'min:5'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.user_id' => ['required', 'exists:users,id'],
            'entries.*.hours_worked' => ['required', 'numeric', 'min:0', 'max:168'],
            'entries.*.notes' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'], // Allow updating header comments too
        ];
    }
}
