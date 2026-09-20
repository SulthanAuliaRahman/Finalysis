<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $auth_user = $this->user();

        return $auth_user && $auth_user->role === 'super_admin';
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $authUser = $this->user();
            $user = $this->route('user');

            if (! $authUser || ! $user) {
                return;
            }

            if ($authUser->is($user)) {

                if (! $this->boolean('is_active')) {
                    $validator->errors()->add(
                        'is_active',
                        'Anda tidak dapat menonaktifkan akun Anda sendiri.'
                    );
                }

                if ($this->input('role') !== 'super_admin') {
                    $validator->errors()->add(
                        'role',
                        'Anda tidak dapat mengubah role akun Anda sendiri.'
                    );
                }
            }
        });
    }
}