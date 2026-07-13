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
            'llm_provider' => ['required', 'in:openai,gemini,ollama,localai'],
            'llm_url' => ['nullable', 'url', 'max:255'],
            'llm_model' => ['required', 'string', 'max:100'],
            'llm_api_key' => ['nullable', 'string'],

            'embedding_provider' => ['required', 'in:openai,gemini,ollama,localai'],
            'embedding_url' => ['nullable', 'url', 'max:255'],
            'embedding_model' => ['required', 'string', 'max:100'],
            'embedding_api_key' => ['nullable', 'string'],

            'reranker_provider' => ['required', 'in:none,cohere,jina,localai'],
            'reranker_model' => ['nullable', 'string', 'max:100'],
            'reranker_top_n' => ['nullable', 'integer', 'min:1', 'max:20'],
            'reranker_api_key' => ['nullable', 'string'],
            'localai_url' => ['nullable', 'url', 'max:255'],

            'vector_store_driver' => ['required', 'in:file'],
            'vector_store_path' => ['nullable', 'string', 'max:255'],
            'vector_store_name' => ['required', 'string', 'max:255'],

            'system_prompt' => ['nullable', 'string'],
        ];
    }
}
