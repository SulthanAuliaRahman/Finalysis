<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $auth_user = $this->user();
        return $auth_user && ($auth_user->role === 'super_admin' || $auth_user->role === 'manager');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $auth_user = $this->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_active' => 'required|boolean',
        ];

        if ($auth_user && $auth_user->role === 'manager') {
            $rules['role'] = 'required|string|in:user';
            $rules['perusahaan_id'] = 'required|integer|in:' . $auth_user->perusahaan_id;
        } else {
            $rules['role'] = 'required|string|in:super_admin,manager,user';
            $rules['perusahaan_id'] = 'required|exists:perusahaan,id';
        }

        return $rules;
    }
}
