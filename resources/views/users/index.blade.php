@extends('layouts.app', ['title' => 'Usuarios'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Usuarios</h1>
        <a href="/users/new" class="border rounded-lg px-4 py-2">Nuevo usuario</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-auto border rounded-lg">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">Nombre</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Rol</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->email }}</td>
                        <td class="px-3 py-2">{{ $user->role }}</td>
                        <td class="px-3 py-2">
                            @if($user->is_active)
                                <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">
                                    Activo
                                </span>
                            @else
                                <span class="inline-block bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <a href="/users/{{ $user->id }}/edit" class="underline">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection