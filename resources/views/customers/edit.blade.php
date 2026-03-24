@extends('layouts.app', ['title' => 'Editar cliente'])

@section('content')
<div class="max-w-2xl rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Editar cliente</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/customers/{{ $customer->id }}" class="grid gap-4">
        @csrf
        @method('PUT')

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Nombre</span>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">NIT</span>
            <input type="text" name="nit" value="{{ old('nit', $customer->nit) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Teléfono</span>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Correo</span>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Dirección</span>
            <input type="text" name="address" value="{{ old('address', $customer->address) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Tipo de cliente</span>
            <select name="customer_type" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                <option value="credit" @selected(old('customer_type', $customer->customer_type) === 'credit')>Crédito</option>
                <option value="cash" @selected(old('customer_type', $customer->customer_type) === 'cash')>Contado</option>
                <option value="mixed" @selected(old('customer_type', $customer->customer_type) === 'mixed')>Mixto</option>
            </select>
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->is_active))>
            <span>Activo</span>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Notas</span>
            <textarea name="notes" rows="4"
                      class="w-full border border-slate-300 rounded-xl px-3 py-2">{{ old('notes', $customer->notes) }}</textarea>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                Guardar cambios
            </button>

            <a href="/customers" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection