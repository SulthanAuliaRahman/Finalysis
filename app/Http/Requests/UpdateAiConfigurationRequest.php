<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAiConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'llm_provider' => ['required', 'string', 'in:openai,gemini,anthropic,ollama'],
            'llm_model'    => ['required', 'string', 'max:100'],
            'base_url'     => ['nullable', 'required_if:llm_provider,ollama', 'string', 'max:255'],
            'llm_api_key'  => ['nullable', 'string'],
        ];
    }
}
