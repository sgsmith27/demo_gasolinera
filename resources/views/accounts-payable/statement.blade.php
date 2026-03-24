@extends('layouts.app', ['title' => 'Estado de cuenta proveedor'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Estado de cuenta del proveedor</h1>
                <p class="text-sm text-slate-500">Proveedor: {{ $supplier->name }}</p>
            </div>

            <a href="/suppliers/{{ $supplier->id }}/statement/pdf"
               target="_blank"
               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Documentos</div>
            <div class="text-2xl font-bold mt-2">{{ $summary->total_items ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Monto original</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->original_total_q ?? 0), 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Pagado</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->paid_total_q ?? 0), 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Saldo</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($summary->balance_total_q ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[1100px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Documento</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Categoría</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Descripción</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Original</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Pagado</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Saldo</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr>
                            <td class="px-4 py-3">{{ $account->document_date }}</td>
                            <td class="px-4 py-3">{{ $account->document_no ?? '—' }}</td>
                            <td class="px-4 py-3">{{ strtoupper($account->category) }}</td>
                            <td class="px-4 py-3">{{ $account->description }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->original_amount_q, 2) }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->paid_amount_q, 2) }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$account->balance_q, 2) }}</td>
                            <td class="px-4 py-3">{{ strtoupper($account->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-4 text-slate-500">No hay documentos para este proveedor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection