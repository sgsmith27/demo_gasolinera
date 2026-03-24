@extends('layouts.app', ['title' => 'Dashboard de auditoría'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-600 text-white p-4 md:p-6 shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-sm text-slate-200 mb-2">Control administrativo</p>
                <h1 class="text-2xl md:text-3xl font-bold">Dashboard de auditoría</h1>
                <p class="text-sm text-slate-200 mt-2">
                    Visualiza actividad del sistema en el rango seleccionado.
                </p>
            </div>

            <form method="GET" action="/audit-logs/dashboard" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <input type="date" name="from" value="{{ $from }}" class="rounded-xl border border-slate-400/30 bg-white/10 px-3 py-2 text-sm text-white">
                <input type="date" name="to" value="{{ $to }}" class="rounded-xl border border-slate-400/30 bg-white/10 px-3 py-2 text-sm text-white">
                <button type="submit" class="rounded-xl bg-white text-slate-900 px-4 py-2 text-sm font-semibold">
                    Aplicar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total eventos</div>
            <div class="text-2xl md:text-3xl font-bold text-slate-800 mt-2">{{ $totals->total_events ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Anulaciones</div>
            <div class="text-2xl md:text-3xl font-bold text-rose-600 mt-2">{{ $totals->total_void ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Creaciones</div>
            <div class="text-2xl md:text-3xl font-bold text-emerald-600 mt-2">{{ $totals->total_create ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Actualizaciones</div>
            <div class="text-2xl md:text-3xl font-bold text-amber-600 mt-2">{{ $totals->total_update ?? 0 }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Ajustes</div>
            <div class="text-2xl md:text-3xl font-bold text-blue-600 mt-2">{{ $totals->total_adjust ?? 0 }}</div>
        </div>
    </div>

    <div id="audit-chart-data"
         data-module-labels='@json($moduleLabels)'
         data-module-values='@json($moduleValues)'
         data-action-labels='@json($actionLabels)'
         data-action-values='@json($actionValues)'
         data-days-labels='@json($daysLabels)'
         data-days-values='@json($daysValues)'>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 md:gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Eventos por módulo</h2>
                <p class="text-sm text-slate-500">Distribución por área del sistema.</p>
            </div>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="auditByModuleChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Eventos por acción</h2>
                <p class="text-sm text-slate-500">Tipo de operación registrada.</p>
            </div>
            <div class="relative w-full" style="height: 280px;">
                <canvas id="auditByActionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-800">Actividad últimos 7 días</h2>
            <p class="text-sm text-slate-500">Cantidad de eventos por día.</p>
        </div>
        <div class="relative w-full" style="height: 300px;">
            <canvas id="auditByDayChart"></canvas>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 md:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Últimos eventos</h2>
                <p class="text-sm text-slate-500">Actividad reciente registrada en el sistema.</p>
            </div>
            <a href="/audit-logs" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Ver bitácora</a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 w-full">
            <table class="min-w-[1000px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Usuario</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Módulo</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Acción</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($latestLogs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">{{ $log->event_at }}</td>
                            <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $log->module }}</td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-slate-500">No hay eventos registrados en el rango.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = document.getElementById('audit-chart-data');

    const moduleLabels = JSON.parse(chartData.getAttribute('data-module-labels') || '[]');
    const moduleValues = JSON.parse(chartData.getAttribute('data-module-values') || '[]');
    const actionLabels = JSON.parse(chartData.getAttribute('data-action-labels') || '[]');
    const actionValues = JSON.parse(chartData.getAttribute('data-action-values') || '[]');
    const daysLabels = JSON.parse(chartData.getAttribute('data-days-labels') || '[]');
    const daysValues = JSON.parse(chartData.getAttribute('data-days-values') || '[]');

    if (!window.Chart) {
        console.error('Chart.js no está disponible en window.Chart');
        return;
    }

    const moduleCanvas = document.getElementById('auditByModuleChart');
    const actionCanvas = document.getElementById('auditByActionChart');
    const dayCanvas = document.getElementById('auditByDayChart');

    if (moduleCanvas) {
        new window.Chart(moduleCanvas, {
            type: 'bar',
            data: {
                labels: moduleLabels,
                datasets: [{
                    label: 'Eventos',
                    data: moduleValues,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    }

    if (actionCanvas) {
        new window.Chart(actionCanvas, {
            type: 'doughnut',
            data: {
                labels: actionLabels,
                datasets: [{
                    label: 'Eventos',
                    data: actionValues,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    if (dayCanvas) {
        new window.Chart(dayCanvas, {
            type: 'line',
            data: {
                labels: daysLabels,
                datasets: [{
                    label: 'Eventos',
                    data: daysValues,
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }
});
</script>
@endsection
