<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Perusahaan;
use Inertia\Inertia;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $auth_user = $request->user();
        $filters = $request->only([
            'search',
            'role',
            'status',
        ]);
        
        $query = User::query()->with('perusahaan:id,nama');

        if ($auth_user->role === 'manager') {
            $query->where('perusahaan_id', $auth_user->perusahaan_id)
                  ->where('role', 'user');
            $filters['role'] = 'user';
        }

        $users = $query->filter($filters)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $auth_user = $request->user();
        
        if ($auth_user->role === 'manager') {
            $perusahaanList = Perusahaan::select('id', 'nama')
                ->where('id', $auth_user->perusahaan_id)
                ->get();
        } else {
            $perusahaanList = Perusahaan::select('id', 'nama')
                ->orderBy('nama')
                ->get();
        }

        return Inertia::render('Users/Create', [
            'perusahaanList' => $perusahaanList,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user)
    {
        $auth_user = $request->user();

        // Authorization check
        if ($auth_user->role === 'manager') {
            if ($user->role !== 'user' || $user->perusahaan_id !== $auth_user->perusahaan_id) {
                abort(403);
            }
            $perusahaanList = Perusahaan::select('id', 'nama')
                ->where('id', $auth_user->perusahaan_id)
                ->get();
        } else {
            $perusahaanList = Perusahaan::select('id', 'nama')
                ->orderBy('nama')
                ->get();
        }

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'perusahaanList' => $perusahaanList,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $auth_user = $request->user();

        if ($user->id === $auth_user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Authorization check
        if ($auth_user->role === 'manager') {
            if ($user->role !== 'user' || $user->perusahaan_id !== $auth_user->perusahaan_id) {
                abort(403);
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
