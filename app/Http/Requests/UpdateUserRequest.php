<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $auth_user = $this->user();
        return $auth_user && $auth_user->role === 'super_admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?? ''),
            'role' => 'required|string|in:super_admin,user',
            'perusahaan_id' => 'required|exists:perusahaan,id',
            'is_active' => 'required|boolean',
        ];

        if ($this->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }
}
