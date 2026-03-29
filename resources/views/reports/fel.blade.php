@extends('layouts.app', ['title' => 'Reporte FEL'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">

    <h1 class="text-xl font-semibold mb-4">Reporte FEL (Libro de Ventas)</h1>

    <form method="GET" class="grid md:grid-cols-6 gap-3 mb-4">
        <input type="date" name="from" value="{{ $from }}" class="border rounded-lg px-3 py-2">
        <input type="date" name="to" value="{{ $to }}" class="border rounded-lg px-3 py-2">

        <select name="status" class="border rounded-lg px-3 py-2">
            <option value="">Todos</option>
            <option value="certified" {{ $status == 'certified' ? 'selected' : '' }}>Certificados</option>
            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Anulados</option>
            <option value="error" {{ $status == 'error' ? 'selected' : '' }}>Error</option>
        </select>

        <button class="bg-black text-white rounded-lg px-4 py-2">
            Filtrar
        </button>

        <a
        href="{{ route('reports.fel.pdf', ['from' => $from, 'to' => $to, 'status' => $status]) }}"
        class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-4 py-2 text-white hover:bg-rose-700"
        target="_blank"
    >
        Exportar PDF
    </a>

    <a
        href="{{ route('reports.fel.excel', ['from' => $from, 'to' => $to, 'status' => $status]) }}"
        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700"
    >
        Exportar Excel
    </a>
    </form>

    <div class="grid md:grid-cols-7 gap-3 mb-4">
    <div class="rounded-xl border bg-slate-50 p-4">
        <div class="text-xs text-slate-500">Documentos</div>
        <div class="text-lg font-semibold text-slate-800">{{ $totals['count'] }}</div>
    </div>

    <div class="rounded-xl border bg-emerald-50 p-4">
        <div class="text-xs text-emerald-600">Certificados</div>
        <div class="text-lg font-semibold text-emerald-700">{{ $totals['certified_count'] }}</div>
    </div>

    <div class="rounded-xl border bg-slate-100 p-4">
        <div class="text-xs text-slate-600">Anulados</div>
        <div class="text-lg font-semibold text-slate-700">{{ $totals['cancelled_count'] }}</div>
    </div>

    <div class="rounded-xl border bg-rose-50 p-4">
        <div class="text-xs text-rose-600">Con error</div>
        <div class="text-lg font-semibold text-rose-700">{{ $totals['error_count'] }}</div>
    </div>

    <div class="rounded-xl border bg-amber-50 p-4">
        <div class="text-xs text-amber-600">Monto total</div>
        <div class="text-lg font-semibold text-amber-700">
            Q{{ number_format((float) $totals['total_amount'], 2) }}
        </div>
    </div>

    <div class="rounded-xl border bg-blue-50 p-4">
        <div class="text-xs text-blue-600">Base imponible</div>
        <div class="text-lg font-semibold text-blue-700">
            Q{{ number_format((float) $totals['taxable_base'], 2) }}
        </div>
    </div>

    <div class="rounded-xl border bg-violet-50 p-4">
        <div class="text-xs text-violet-600">IVA débito fiscal estimado 12%</div>
        <div class="text-lg font-semibold text-violet-700">
            Q{{ number_format((float) $totals['vat_amount'], 2) }}
        </div>
    </div>


</div>

    <div class="overflow-auto border rounded-lg">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2">Fecha</th>
                    <th class="px-3 py-2">Venta</th>
                    <th class="px-3 py-2">UUID</th>
                    <th class="px-3 py-2">Serie</th>
                    <th class="px-3 py-2">Número</th>
                    <th class="px-3 py-2">Cliente</th>
                    <th class="px-3 py-2">NIT/CUI</th>
                    <th class="px-3 py-2">Total</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2">Usuario</th>
                </tr>
            </thead>

            <tbody>
                @foreach($documents as $doc)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $doc->issued_at }}</td>
                        <td class="px-3 py-2">#{{ $doc->sale_id }}</td>
                        <td class="px-3 py-2 text-xs break-all">{{ $doc->uuid }}</td>
                        <td class="px-3 py-2">{{ $doc->series }}</td>
                        <td class="px-3 py-2">{{ $doc->number }}</td>
                        <td class="px-3 py-2">
                            {{ $doc->receiver_name ?? 'CONSUMIDOR FINAL' }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $doc->receiver_taxid ?? 'CF' }}
                        </td>
                        <td class="px-3 py-2">
                            Q{{ number_format((float) ($doc->total_amount_q ?? $doc->sale->total_amount_q ?? 0), 2) }}
                        </td>
                        <td class="px-3 py-2">
                            @if($doc->fel_status === 'certified')
                                <span class="text-green-600">Certificado</span>
                            @elseif($doc->fel_status === 'cancelled')
                                <span class="text-gray-600">Anulado</span>
                            @else
                                <span class="text-red-600">Error</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $doc->sale->user->name ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection