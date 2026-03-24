@extends('layouts.app', ['title' => 'Nueva cuenta por pagar'])

@section('content')
<div class="max-w-3xl rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Nueva cuenta por pagar</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/accounts-payable" class="grid gap-4">
        @csrf

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Proveedor</span>
            <select name="supplier_id" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                        {{ $supplier->name }} @if($supplier->nit) — {{ $supplier->nit }} @endif
                    </option>
                @endforeach
            </select>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Fecha documento</span>
                <input type="date" name="document_date" value="{{ old('document_date', $today) }}"
                       class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">No. documento</span>
                <input type="text" name="document_no" value="{{ old('document_no') }}"
                       class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Categoría</span>
                <select name="category" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                    <option value="fuel" @selected(old('category') === 'fuel')>Combustible</option>
                    <option value="services" @selected(old('category') === 'services')>Servicios</option>
                    <option value="maintenance" @selected(old('category') === 'maintenance')>Mantenimiento</option>
                    <option value="general" @selected(old('category') === 'general')>General</option>
                </select>
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Monto original</span>
                <input type="number" step="0.01" min="0.01" name="original_amount_q" value="{{ old('original_amount_q') }}"
                       class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>
        </div>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Descripción</span>
            <input type="text" name="description" value="{{ old('description') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
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

            <a href="/accounts-payable" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection