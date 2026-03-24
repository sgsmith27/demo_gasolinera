<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Support\Audit;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = ['admin', 'supervisor', 'despachador'];
        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $request->validated('role'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
        ]);

        Audit::log(
            module: 'users',
            action: 'create',
            entityType: 'User',
            entityId: $user->id,
            description: 'Usuario creado',
            meta: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]
        );


        return redirect('/users')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        $roles = ['admin', 'supervisor', 'despachador'];
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'is_active' => (bool) ($request->validated('is_active') ?? false),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $user->update($data);
        Audit::log(
            module: 'users',
            action: 'update',
            entityType: 'User',
            entityId: $user->id,
            description: 'Usuario actualizado',
            meta: [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ]
        );


        return redirect('/users')->with('success', 'Usuario actualizado correctamente.');
    }
}