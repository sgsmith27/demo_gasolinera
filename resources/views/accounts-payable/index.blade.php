@extends('layouts.app', ['title' => 'Cuentas por pagar'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-slate-800">Cuentas por pagar</h1>
            <p class="text-sm text-slate-500">Obligaciones pendientes con proveedores.</p>
        </div>

        <form method="GET" action="/accounts-payable" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end mb-5">
            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Desde</span>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Hasta</span>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Proveedor</span>
                <select name="supplier_id" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                    <option value="">Todos</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string)$supplierId === (string)$supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Estado</span>
                <select name="status" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                    <option value="">Todos</option>
                    <option value="pending" @selected($status === 'pending')>Pendiente</option>
                    <option value="paid" @selected($status === 'paid')>Pagada</option>
                </select>
            </label>

            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                    Filtrar
                </button>

                <a href="/accounts-payable/pdf?from={{ $from }}&to={{ $to }}&supplier_id={{ $supplierId }}&status={{ $status }}"
                   target="_blank"
                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                    PDF
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-5">
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Documentos</div>
                <div class="text-2xl font-bold mt-2">{{ $summary->total_items ?? 0 }}</div>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Monto original</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->original_total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Pagado</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->paid_total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Saldo</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->balance_total_q ?? 0), 2) }}</div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[1200px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Proveedor</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Documento</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Categoría</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Descripción</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Original</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Pagado</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Saldo</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Estado</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $account->document_date }}</td>
                            <td class="px-4 py-3">{{ $account->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $account->document_no ?? '—' }}</td>
                            <td class="px-4 py-3">{{ strtoupper($account->category) }}</td>
                            <td class="px-4 py-3">{{ $account->description }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->original_amount_q, 2) }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->paid_amount_q, 2) }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->balance_q, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($account->status === 'paid')
                                    <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-2 py-1 text-xs font-medium">Pagada</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-2 py-1 text-xs font-medium">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 flex gap-3">
                                <a href="/accounts-payable/{{ $account->id }}" class="text-indigo-600 hover:underline">Ver detalle</a>
                                <a href="/suppliers/{{ $account->supplier_id }}/statement" class="text-slate-700 hover:underline">Estado de cuenta</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-4 text-slate-500">No hay cuentas por pagar registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection