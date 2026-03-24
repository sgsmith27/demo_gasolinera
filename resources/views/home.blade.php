@extends('layouts.app', ['title' => 'Inicio'])

@section('content')
<div class="grid gap-4 md:grid-cols-2">
    <a href="/sales/new" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
        <div class="text-lg font-semibold">Nueva venta</div>
        <div class="text-sm text-gray-600">Registro rápido para despachadores</div>
    </a>

    <a href="/reports/daily-close" class="block bg-white rounded-xl shadow-sm border p-5 hover:shadow">
        <div class="text-lg font-semibold">Cierre diario</div>
        <div class="text-sm text-gray-600">Resumen por bomba, despachador y combustible</div>
    </a>
</div>
@endsection