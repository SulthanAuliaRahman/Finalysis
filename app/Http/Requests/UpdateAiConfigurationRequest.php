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
            'llm_provider' => ['nullable', 'string', 'max:50'],
            'llm_url' => ['nullable', 'string', 'max:255'],
            'llm_model' => ['nullable', 'string', 'max:100'],
            'llm_api_key' => ['nullable', 'string'],

            'embedding_provider' => ['nullable', 'string', 'max:50'],
            'embedding_url' => ['nullable', 'string', 'max:255'],
            'embedding_model' => ['nullable', 'string', 'max:100'],
            'embedding_api_key' => ['nullable', 'string'],

            'reranker_provider' => ['nullable', 'string', 'max:50'],
            'reranker_model' => ['nullable', 'string', 'max:100'],
            'reranker_top_n' => ['nullable', 'integer', 'min:1', 'max:20'],
            'reranker_api_key' => ['nullable', 'string'],
            'localai_url' => ['nullable', 'string', 'max:255'],

            'vector_store_driver' => ['nullable', 'string', 'max:255'],
            'vector_store_path' => ['nullable', 'string', 'max:255'],
            'vector_store_name' => ['nullable', 'string', 'max:255'],

            'system_prompt' => ['nullable', 'string'],
        ];
    }
}
