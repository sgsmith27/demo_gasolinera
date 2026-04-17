@extends('layouts.app', ['title' => 'Balance operativo'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-slate-800">Balance operativo</h1>
            <p class="text-sm text-slate-500">
                Ventas + Cobros CxC - Abastecimientos - Gastos - Pagos CxP
            </p>
        </div>

        <form method="GET" action="/reports/financial-summary" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end mb-5">
            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Desde</span>
                <input type="date" name="from" value="{{ $from }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-slate-600">Hasta</span>
                <input type="date" name="to" value="{{ $to }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
            </label>

            <div class="flex gap-2">
                <button type="submit"
                    class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                    Filtrar
                </button>

                <a href="/reports/financial-summary/pdf?from={{ $from }}&to={{ $to }}"
                   target="_blank"
                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                    PDF
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-7 gap-4 mb-5">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                <div class="text-sm text-emerald-700">Ventas</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($sales->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-4">
                <div class="text-sm text-blue-700">Cobros CxC</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($receivableCollections->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-cyan-50 border border-cyan-200 p-4">
                <div class="text-sm text-cyan-700">Abastecimientos</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($fuelDeliveries->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                <div class="text-sm text-rose-700">Gastos</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($expenses->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
                <div class="text-sm text-amber-700">Pagos CxP</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($payablePayments->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-slate-100 border border-slate-300 p-4">
                <div class="text-sm text-slate-700">Balance final</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$finalBalance, 2) }}</div>
            </div>

                    <div class="rounded-2xl bg-indigo-50 border border-indigo-200 p-4">
            <div class="text-sm text-indigo-700">Margen bruto</div>
            <div class="text-2xl font-bold mt-2">
                Q{{ number_format((float)$grossMargin, 2) }}
            </div>
        </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div class="rounded-2xl bg-white border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Saldo pendiente por cobrar</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($pendingReceivables->total_q ?? 0), 2) }}</div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-4">
                <div class="text-sm text-slate-500">Saldo pendiente por pagar</div>
                <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($pendingPayables->total_q ?? 0), 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 p-4">
                <h2 class="text-lg font-semibold text-slate-800 mb-3">Resumen del período</h2>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-600">Concepto</th>
                                <th class="px-4 py-3 font-semibold text-slate-600">Cantidad</th>
                                <th class="px-4 py-3 font-semibold text-slate-600">Total Q</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-3">Ventas</td>
                                <td class="px-4 py-3">{{ $sales->total_sales ?? 0 }}</td>
                                <td class="px-4 py-3">Q{{ number_format((float)($sales->total_q ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3">Abastecimientos</td>
                                <td class="px-4 py-3">{{ $fuelDeliveries->total_items ?? 0 }}</td>
                                <td class="px-4 py-3">Q{{ number_format((float)($fuelDeliveries->total_q ?? 0), 2) }}</td>
                            </tr>

                            <tr>
                                <td class="px-4 py-3">Gastos</td>
                                <td class="px-4 py-3">{{ $expenses->total_items ?? 0 }}</td>
                                <td class="px-4 py-3">Q{{ number_format((float)($expenses->total_q ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3">Cobros CxC</td>
                                <td class="px-4 py-3">{{ $receivableCollections->total_items ?? 0 }}</td>
                                <td class="px-4 py-3">Q{{ number_format((float)($receivableCollections->total_q ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3">Pagos CxP</td>
                                <td class="px-4 py-3">{{ $payablePayments->total_items ?? 0 }}</td>
                                <td class="px-4 py-3">Q{{ number_format((float)($payablePayments->total_q ?? 0), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 p-4">
                <h2 class="text-lg font-semibold text-slate-800 mb-3">Ventas por método de pago</h2>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-slate-600">Método</th>
                                <th class="px-4 py-3 font-semibold text-slate-600">Ventas</th>
                                <th class="px-4 py-3 font-semibold text-slate-600">Total Q</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($salesByPayment as $row)
                                <tr>
                                    <td class="px-4 py-3">{{ $row->payment_method }}</td>
                                    <td class="px-4 py-3">{{ $row->total_sales }}</td>
                                    <td class="px-4 py-3">Q{{ number_format((float)$row->total_q, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-4 text-slate-500">No hay ventas en el rango.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
