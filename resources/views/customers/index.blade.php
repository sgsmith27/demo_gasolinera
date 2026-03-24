@extends('layouts.app', ['title' => 'Clientes'])

@section('content')
<div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Clientes</h1>
            <p class="text-sm text-slate-500">Clientes para ventas al crédito o registro comercial.</p>
        </div>

        <a href="/customers/new" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
            Nuevo cliente
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-[900px] w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-slate-600">Nombre</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">NIT</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Teléfono</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Correo</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Tipo</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $customer->name }}</td>
                        <td class="px-4 py-3">{{ $customer->nit ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $customer->email ?? '—' }}</td>
                        <td class="px-4 py-3">{{ strtoupper($customer->customer_type) }}</td>
                        <td class="px-4 py-3">
                            @if($customer->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-2 py-1 text-xs font-medium">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs font-medium">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="/customers/{{ $customer->id }}/edit" class="text-indigo-600 hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-slate-500">No hay clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection