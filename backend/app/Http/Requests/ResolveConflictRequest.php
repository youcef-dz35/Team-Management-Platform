<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ResolveConflictRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        // Only executives can resolve conflicts
        return $user && $user->hasAnyRole(['ceo', 'cfo', 'gm', 'ops_manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resolution_notes' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resolution_notes.required' => 'Resolution notes are required when resolving a conflict.',
            'resolution_notes.min' => 'Resolution notes must be at least :min characters to provide adequate context.',
            'resolution_notes.max' => 'Resolution notes cannot exceed :max characters.',
        ];
    }
}
