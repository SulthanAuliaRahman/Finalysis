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
        if (!$auth_user || !in_array($auth_user->role, ['super_admin', 'manager'])) {
            return false;
        }

        // Jika manager, cek apakah user yang diedit ber-role 'user' dan di perusahaan yang sama
        if ($auth_user->role === 'manager') {
            $user = $this->route('user');
            $userModel = $user instanceof \App\Models\User ? $user : \App\Models\User::find($user);
            
            if (!$userModel || $userModel->role !== 'user' || $userModel->perusahaan_id !== $auth_user->perusahaan_id) {
                return false;
            }
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
        $auth_user = $this->user();
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($userId ?? ''),
            'is_active' => 'required|boolean',
        ];

        if ($auth_user && $auth_user->role === 'manager') {
            $rules['role'] = 'required|string|in:user';
            $rules['perusahaan_id'] = 'required|integer|in:' . $auth_user->perusahaan_id;
        } else {
            $rules['role'] = 'required|string|in:super_admin,manager,user';
            $rules['perusahaan_id'] = 'required|exists:perusahaan,id';
        }

        if ($this->filled('password')) {
            $rules['password'] = 'required|string|min:8';
        }

        return $rules;
    }
}
