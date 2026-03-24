@extends('layouts.app', ['title' => 'Nuevo usuario'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Nuevo usuario</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/users" class="grid gap-3">
        @csrf

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Nombre</span>
            <input name="name" type="text" value="{{ old('name') }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Contraseña</span>
            <input name="password" type="password" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Rol</span>
            <select name="role" class="w-full border rounded-lg px-3 py-2">
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" checked>
            <span>Activo</span>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="bg-black text-white rounded-lg px-4 py-2">
                Guardar
            </button>
            <a href="/users" class="border rounded-lg px-4 py-2">Cancelar</a>
        </div>
    </form>
</div>
@endsection