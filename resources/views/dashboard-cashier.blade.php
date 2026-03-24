@extends('layouts.app', ['title' => 'Panel de despachador'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-500 text-white p-4 md:p-6 shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-sm text-slate-200 mb-2">Bienvenido</p>
                <h1 class="text-2xl md:text-3xl font-bold break-words">{{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-200 mt-2">
                    Resumen de tus ventas del día.
                </p>
            </div>

            <div>
                <a href="/sales/new"
                   class="inline-flex items-center rounded-xl bg-white text-slate-900 px-5 py-3 text-sm font-semibold shadow hover:bg-slate-100 transition">
                    Nueva venta
                </a>
            </div>
        </div>
    </div>

    @if($currentShift)
    <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="text-sm text-slate-500">Turno actual</div>
                    <div class="text-lg font-semibold text-slate-800">
                        Turno #{{ $currentShift->id }} — abierto desde {{ $currentShift->started_at }}
                    </div>
                    <div class="text-sm text-slate-500 mt-1">
                        Fondo inicial: Q{{ number_format((float)$currentShift->opening_cash_q, 2) }}
                    </div>
                </div>
                <div class="text-sm text-slate-500 mt-1">
                    Asignación:
                    @if($currentShift->assignment_mode === 'fixed')
                        Bomba fija — {{ $currentShift->pump?->code ?? '—' }}
                    @else
                        Libre
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-700">
            No tienes turno abierto. Solicita a un supervisor o administrador que abra tu turno.
        </div>
    @endif

    @if($currentShift && $shiftSalesSummary)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Ventas del turno</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">{{ $shiftSalesSummary->total_sales }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total del turno</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">
                Q{{ number_format((float)$shiftSalesSummary->total_q, 2) }}
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Galones del turno</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">
                {{ number_format((float)$shiftSalesSummary->total_gallons, 3) }}
            </div>
        </div>
    </div>
@endif



    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Ventas de hoy</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">{{ $salesToday->total_sales }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total vendido hoy</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2 break-words">
                Q{{ number_format((float)$salesToday->total_q, 2) }}
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="text-sm text-slate-500">Galones vendidos hoy</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">
                {{ number_format((float)$salesToday->total_gallons, 3) }}
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Tus últimas ventas</h2>
                <p class="text-sm text-slate-500">Últimos registros realizados por ti.</p>
            </div>

            <a href="/sales/new" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                Registrar otra venta
            </a>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 w-full">
            <table class="min-w-[700px] w-full text-left text-sm">
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
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $sale->id }}</td>
                            <td class="px-4 py-3">{{ $sale->sold_at }}</td>
                            <td class="px-4 py-3">{{ $sale->fuel_name }}</td>
                            <td class="px-4 py-3">{{ number_format((float)$sale->gallons, 3) }}</td>
                            <td class="px-4 py-3 font-medium text-slate-700">Q{{ number_format((float)$sale->total_amount_q, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-slate-500">No tienes ventas registradas todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-3 md:hidden">
            @forelse($latestSales as $sale)
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-semibold text-slate-800">Venta #{{ $sale->id }}</div>
                        <div class="text-xs text-slate-500">{{ $sale->sold_at }}</div>
                    </div>

                    <div class="grid gap-1 text-sm">
                        <div><span class="text-slate-500">Combustible:</span> <span class="font-medium text-slate-700">{{ $sale->fuel_name }}</span></div>
                        <div><span class="text-slate-500">Galones:</span> <span class="font-medium text-slate-700">{{ number_format((float)$sale->gallons, 3) }}</span></div>
                        <div><span class="text-slate-500">Total:</span> <span class="font-medium text-slate-700">Q{{ number_format((float)$sale->total_amount_q, 2) }}</span></div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 p-4 text-sm text-slate-500">
                    No tienes ventas registradas todavía.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection