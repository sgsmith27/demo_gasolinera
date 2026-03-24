@extends('layouts.app', ['title' => 'Nuevo proveedor'])

@section('content')
<div class="max-w-2xl rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Nuevo proveedor</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/suppliers" class="grid gap-4">
        @csrf

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Nombre</span>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">NIT</span>
            <input type="text" name="nit" value="{{ old('nit') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Teléfono</span>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Correo</span>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Dirección</span>
            <input type="text" name="address" value="{{ old('address') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Tipo de proveedor</span>
            <select name="supplier_type" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                <option value="fuel" @selected(old('supplier_type') === 'fuel')>Combustible</option>
                <option value="services" @selected(old('supplier_type') === 'services')>Servicios</option>
                <option value="maintenance" @selected(old('supplier_type') === 'maintenance')>Mantenimiento</option>
                <option value="general" @selected(old('supplier_type') === 'general')>General</option>
            </select>
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" checked>
            <span>Activo</span>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Notas</span>
            <textarea name="notes" rows="4"
                      class="w-full border border-slate-300 rounded-xl px-3 py-2">{{ old('notes') }}</textarea>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                Guardar
            </button>

            <a href="/suppliers" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection