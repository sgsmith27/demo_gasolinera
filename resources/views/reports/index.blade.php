@extends('layouts.app', ['title' => 'Reportes'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Reportes</h1>
    <p class="text-sm text-gray-600 mb-6">
        Selecciona el reporte que deseas consultar.
    </p>

    <div class="grid gap-4 md:grid-cols-2">
        <a href="/reports/daily-close" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Cierre diario</div>
            <div class="text-sm text-gray-600">Resumen de ventas del día por bomba, despachador y combustible.</div>
        </a>

        <a href="/reports/sales-summary" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Resumen por rango</div>
            <div class="text-sm text-gray-600">Totales de ventas por rango de fechas.</div>
        </a>

        <a href="/reports/cashier-close" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Cuadre despachador</div>
            <div class="text-sm text-gray-600">Cuadre diario por despachador con métodos de pago y combustibles.</div>
        </a>

        <a href="/reports/cashier-close-range" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Cuadre rango</div>
            <div class="text-sm text-gray-600">Cuadre por despachador en un rango de fechas.</div>
        </a>

        <a href="/work-shifts-report" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Reporte de turnos</div>
            <div class="text-sm text-gray-600">Consulta turnos por fecha, estado y cierre financiero.</div>
        </a>

        <a href="/reports/financial-summary" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
            <div class="text-lg font-semibold">Balance operativo</div>
            <div class="text-sm text-gray-600">Ventas, gastos, cobros, pagos y balance final por rango.</div>
        </a>

    </div>
</div>
@endsection