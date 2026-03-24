@extends('layouts.app', ['title' => 'Editar usuario'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Editar usuario</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/users/{{ $user->id }}" class="grid gap-3">
        @csrf
        @method('PUT')

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Nombre</span>
            <input name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Email</span>
            <input name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Nueva contraseña (opcional)</span>
            <input name="password" type="password" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Rol</span>
            <select name="role" class="w-full border rounded-lg px-3 py-2">
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
            <span>Activo</span>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="bg-black text-white rounded-lg px-4 py-2">
                Guardar cambios
            </button>
            <a href="/users" class="border rounded-lg px-4 py-2">Cancelar</a>
        </div>
    </form>
</div>
@endsection