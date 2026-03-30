@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-600 text-white p-4 md:p-6 shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-sm text-slate-200 mb-2">Bienvenido de nuevo</p>
                <h1 class="text-2xl md:text-3xl font-bold break-words">{{ auth()->user()->name }}</h1>
                <p class="text-sm text-slate-200 mt-2">
                    Resumen operativo de la gasolinera para hoy.
                </p>
            </div>

            <div class="flex gap-2 md:gap-3 flex-wrap">
                <a href="/sales/new"
                   class="inline-flex items-center rounded-xl bg-white text-slate-900 px-4 py-2 text-sm font-semibold shadow hover:bg-slate-100 transition">
                    Nueva venta
                </a>

                @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
                <a href="/sales"
                       class="inline-flex items-center rounded-xl bg-slate-800/60 border border-slate-600 px-4 py-2 text-sm font-medium hover:bg-slate-700 transition">
                        Ventas Generales
                    </a>    
                
                <a href="/fuel-deliveries/new"
                       class="inline-flex items-center rounded-xl bg-slate-800/60 border border-slate-600 px-4 py-2 text-sm font-medium hover:bg-slate-700 transition">
                        Abastecer
                    </a>

                    <a href="/inventory-adjustments/new"
                       class="inline-flex items-center rounded-xl bg-slate-800/60 border border-slate-600 px-4 py-2 text-sm font-medium hover:bg-slate-700 transition">
                        Ajuste inventario
                    </a>

                    <a href="/reports"
                       class="inline-flex items-center rounded-xl bg-slate-800/60 border border-slate-600 px-4 py-2 text-sm font-medium hover:bg-slate-700 transition">
                        Ver reportes
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">Ventas hoy</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-800 mt-2 break-words">{{ $salesToday->total_sales }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    V
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total vendido hoy</p>
                   <h3 class="text-2xl md:text-3xl font-bold text-slate-800 mt-2 break-words">Q{{ number_format((float)$salesToday->total_q, 2) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    Q
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">Galones vendidos hoy</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-800 mt-2 break-words">{{ number_format((float)$salesToday->total_gallons, 3) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    G
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">Gastos del mes</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-800 mt-2 break-words">Q{{ number_format((float)$expensesMonth->total_q, 2) }}</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    $
                </div>
            </div>
        </div>
    </div>

    <div id="dashboard-chart-data"
         data-sales-by-fuel-labels='@json($salesByFuelLabels)'
         data-sales-by-fuel-totals='@json($salesByFuelTotals)'
         data-sales-7days-labels='@json($sales7DaysLabels)'
         data-sales-7days-totals='@json($sales7DaysTotals)'
         data-inventory-labels='@json($inventoryChartLabels)'
         data-inventory-values='@json($inventoryChartValues)'>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
        <div class="xl:col-span-2 rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Ventas últimos 7 días</h2>
                    <p class="text-sm text-slate-500">Comportamiento diario de ventas en quetzales.</p>
                </div>
            </div>

            <div class="relative w-full" style="height: 260px; min-width: 0;">
                <canvas id="sales7DaysChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Alertas</h2>
                <p class="text-sm text-slate-500">Tanques con nivel bajo.</p>
            </div>

            @if($alerts->isEmpty())
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    No hay alertas activas de inventario bajo.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                            <div class="text-sm font-semibold text-rose-700">
                                Tanque #{{ $alert->id }} — {{ $alert->fuel_name }}
                            </div>
                            <div class="text-sm text-rose-600 mt-1">
                                Disponible: {{ number_format((float)$alert->current_gallons, 3) }} gal
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 md:gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Ventas por combustible (hoy)</h2>
                <p class="text-sm text-slate-500">Distribución del total vendido hoy.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
                <canvas id="salesByFuelChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Inventario actual por tanque</h2>
                <p class="text-sm text-slate-500">Disponibilidad actual en galones.</p>
            </div>

            <div class="relative w-full" style="height: 260px; min-width: 0;">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-4 md:gap-6">
        <div class="xl:col-span-3 rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Últimas ventas</h2>
                    <p class="text-sm text-slate-500">Movimientos recientes registrados en el sistema.</p>
                </div>
                <a href="/sales" class="text-sm font-medium text-orange-600 hover:text-orange-700">
                    Ver todas
                </a>
            </div>

            <div class="overflow-auto rounded-xl border border-slate-200 w-full">
                <table class="min-w-[700px] w-full text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-slate-600">ID</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Usuario</th>
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
                                <td class="px-4 py-3">{{ $sale->user_name }}</td>
                                <td class="px-4 py-3">{{ $sale->fuel_name }}</td>
                                <td class="px-4 py-3">{{ number_format((float)$sale->gallons, 3) }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700">Q{{ number_format((float)$sale->total_amount_q, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-slate-500">No hay ventas recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm min-w-0">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Inventario actual</h2>
                <p class="text-sm text-slate-500">Estado de los tanques activos.</p>
            </div>

            <div class="space-y-3">
                @foreach($inventory as $tank)
                    @php
                        $current = (float) $tank->current_gallons;
                        $capacity = (float) ($tank->capacity_gallons ?? 0);
                        $percent = $capacity > 0 ? min(100, round(($current / $capacity) * 100, 1)) : null;
                    @endphp

                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-700">
                                    {{ $tank->fuel_name }} — Tanque #{{ $tank->id }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ number_format($current, 3) }} gal
                                    @if($capacity > 0)
                                        de {{ number_format($capacity, 3) }} gal
                                    @endif
                                </div>
                            </div>

                            @if($percent !== null)
                                <div class="text-xs font-semibold text-slate-500">
                                    {{ $percent }}%
                                </div>
                            @endif
                        </div>

                        @if($percent !== null)
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-2 rounded-full
                                    @if($percent <= 20) bg-rose-500
                                    @elseif($percent <= 50) bg-amber-500
                                    @else bg-emerald-500
                                    @endif"
                                    style="width: {{ $percent }}%"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = document.getElementById('dashboard-chart-data');

    const salesByFuelLabels = JSON.parse(chartData.getAttribute('data-sales-by-fuel-labels') || '[]');
    const salesByFuelTotals = JSON.parse(chartData.getAttribute('data-sales-by-fuel-totals') || '[]');
    const sales7DaysLabels = JSON.parse(chartData.getAttribute('data-sales-7days-labels') || '[]');
    const sales7DaysTotals = JSON.parse(chartData.getAttribute('data-sales-7days-totals') || '[]');
    const inventoryLabels = JSON.parse(chartData.getAttribute('data-inventory-labels') || '[]');
    const inventoryValues = JSON.parse(chartData.getAttribute('data-inventory-values') || '[]');

    if (!window.Chart) {
        console.error('Chart.js no está disponible en window.Chart');
        return;
    }

    const fuelCanvas = document.getElementById('salesByFuelChart');
    const daysCanvas = document.getElementById('sales7DaysChart');
    const inventoryCanvas = document.getElementById('inventoryChart');

    if (daysCanvas) {
        new window.Chart(daysCanvas, {
            type: 'line',
            data: {
                labels: sales7DaysLabels,
                datasets: [{
                    label: 'Ventas Q',
                    data: sales7DaysTotals,
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
            }
        });
    }

    if (fuelCanvas) {
        new window.Chart(fuelCanvas, {
            type: 'doughnut',
            data: {
                labels: salesByFuelLabels,
                datasets: [{
                    label: 'Ventas Q',
                    data: salesByFuelTotals,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
            }
        });
    }

    if (inventoryCanvas) {
        new window.Chart(inventoryCanvas, {
            type: 'bar',
            data: {
                labels: inventoryLabels,
                datasets: [{
                    label: 'Galones actuales',
                    data: inventoryValues,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
            }
        });
    }
});
</script>
@endsection