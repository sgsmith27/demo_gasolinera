@extends('layouts.app', ['title' => 'Detalle de turno'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Turno #{{ $workShift->id }}</h1>                
                <p class="text-sm text-slate-500">
                    Despachador: {{ $workShift->user?->name ?? '—' }}
                </p>
                 <p class="text-sm text-slate-500 mt-1">
                Asignación:
                @if($workShift->assignment_mode === 'fixed')
                    Bomba fija — {{ $workShift->pump?->code ?? '—' }}
                @else
                    Libre
                @endif<br><br>
                </p>
                <a href="/work-shifts/{{ $workShift->id }}/pdf"
                    target="_blank"
                    class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-medium hover:bg-slate-500 transition">
                        PDF
                    </a>
            </div>
    

            @if($workShift->status === 'open')
            <form method="POST" action="/work-shifts/{{ $workShift->id }}/close"
                class="grid gap-3 w-full md:w-auto md:min-w-[420px]">
                @csrf

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Efectivo esperado al cierre: <span class="font-semibold">Q{{ number_format((float)$expectedCashPreview, 2) }}</span>
                </div>

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Efectivo entregado (Q)</span>
                    <input type="number" step="0.01" min="0" name="delivered_cash_q"
                        class="w-full border border-slate-300 rounded-xl px-3 py-2"
                        placeholder="0.00" required>
                </label>

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Observaciones de cierre</span>
                    <textarea name="closing_notes" rows="2"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2"
                            placeholder="Observaciones de cierre"></textarea>
                </label>

                <div>
                    <button type="submit"
                            class="rounded-xl bg-rose-600 text-white px-4 py-2 text-sm font-medium hover:bg-rose-700 transition">
                        Cerrar turno
                    </button>                    
                </div>
            </form>
        @endif
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Estado</div>
            <div class="text-2xl font-bold mt-2">{{ strtoupper($workShift->status) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Inicio</div>
            <div class="text-lg font-semibold mt-2">{{ $workShift->started_at }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Fin</div>
            <div class="text-lg font-semibold mt-2">{{ $workShift->ended_at ?? '—' }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Fondo inicial</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$workShift->opening_cash_q, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Ventas activas</div>
            <div class="text-2xl font-bold mt-2">{{ $salesSummary->total_sales ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total vendido</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)($salesSummary->total_q ?? 0), 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Galones vendidos</div>
            <div class="text-2xl font-bold mt-2">{{ number_format((float)($salesSummary->total_gallons ?? 0), 3) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">Ventas en efectivo</div>
        <div class="text-2xl font-bold mt-2">
            Q{{ number_format((float)($salesByPayment->firstWhere('payment_method', 'cash')->total_q ?? 0), 2) }}
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">Efectivo esperado</div>
        <div class="text-2xl font-bold mt-2">
            Q{{ number_format((float)($workShift->expected_cash_q ?? $expectedCashPreview), 2) }}
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="text-sm text-slate-500">Efectivo entregado</div>
        <div class="text-2xl font-bold mt-2">
            @if($workShift->delivered_cash_q !== null)
                Q{{ number_format((float)$workShift->delivered_cash_q, 2) }}
            @else
                —
            @endif
        </div>
    </div>
</div>

@if($workShift->status === 'closed')
    <div class="rounded-2xl border p-5 shadow-sm
        @if((float)$workShift->cash_difference_q < 0)
            border-rose-200 bg-rose-50
        @elseif((float)$workShift->cash_difference_q > 0)
            border-amber-200 bg-amber-50
        @else
            border-emerald-200 bg-emerald-50
        @endif">
        <div class="text-sm text-slate-600">Diferencia de efectivo</div>
        <div class="text-3xl font-bold mt-2">
            Q{{ number_format((float)$workShift->cash_difference_q, 2) }}
        </div>

        <div class="text-sm mt-2">
            @if((float)$workShift->cash_difference_q < 0)
                Faltante en caja.
            @elseif((float)$workShift->cash_difference_q > 0)
                Sobrante en caja.
            @else
                Cuadre exacto.
            @endif
        </div>
    </div>
@endif

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Ventas por método de pago</h2>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[600px] w-full text-left text-sm">
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
                            <td colspan="3" class="px-4 py-4 text-slate-500">Sin ventas en este turno.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Últimas ventas del turno</h2>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[800px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">ID</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Combustible</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Galones</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Total Q</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($latestSales as $sale)
                        <tr>
                            <td class="px-4 py-3">{{ $sale->id }}</td>
                            <td class="px-4 py-3">{{ $sale->sold_at }}</td>
                            <td class="px-4 py-3">{{ $sale->fuel?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format((float)$sale->gallons, 3) }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$sale->total_amount_q, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-slate-500">No hay ventas registradas en este turno.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection