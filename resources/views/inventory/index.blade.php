@extends('layouts.app', ['title' => 'Inventario'])

@section('content')
<div class="grid gap-4">
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Inventario</h1>
    <div class="flex gap-2">
        <a href="/fuel-deliveries/new" class="border rounded-lg px-4 py-2">Abastecer combustible</a>
        <a href="/inventory-adjustments/new" class="border rounded-lg px-4 py-2">Ajuste manual</a>
    </div>
</div>

        <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Tanque</th>
                        <th class="px-3 py-2">Combustible</th>
                        <th class="px-3 py-2">Stock actual (gal)</th>
                        <th class="px-3 py-2">Capacidad (gal)</th>
                        <th class="px-3 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tanks as $t)
                        <tr class="border-t">
                            <td class="px-3 py-2">
                                #{{ $t->id }}{{ $t->name ? ' — '.$t->name : '' }}
                            </td>
                            <td class="px-3 py-2">{{ $t->fuel_name }}</td>
                            <td class="px-3 py-2">{{ number_format((float)$t->current_gallons, 3) }}</td>
                            <td class="px-3 py-2">
                                {{ $t->capacity_gallons === null ? '—' : number_format((float)$t->capacity_gallons, 3) }}
                            </td>
                            <td class="px-3 py-2">
                                @if($t->is_active)
                                    <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-block bg-gray-50 text-gray-700 border border-gray-200 px-2 py-0.5 rounded">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-3">Movimientos recientes</h2>

        <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Fecha</th>
                        <th class="px-3 py-2">Tipo</th>
                        <th class="px-3 py-2">Combustible</th>
                        <th class="px-3 py-2">Tanque</th>
                        <th class="px-3 py-2">Delta (gal)</th>
                        <th class="px-3 py-2">Referencia</th>
                        <th class="px-3 py-2">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $m)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $m->moved_at }}</td>
                            <td class="px-3 py-2">
                                @if($m->movement_type === 'IN')
                                    <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">
                                        IN
                                    </span>
                                @elseif($m->movement_type === 'OUT')
                                    <span class="inline-block bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">
                                        OUT
                                    </span>
                                @else
                                    <span class="inline-block bg-gray-50 text-gray-700 border border-gray-200 px-2 py-0.5 rounded">
                                        {{ $m->movement_type }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $m->fuel_name }}</td>
                            <td class="px-3 py-2">#{{ $m->tank_id }}</td>
                            <td class="px-3 py-2">{{ number_format((float)$m->gallons_delta, 3) }}</td>
                            <td class="px-3 py-2">{{ $m->reference_type }} {{ $m->reference_id ?? '' }}</td>
                            <td class="px-3 py-2">{{ $m->created_by_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-500 mt-2">Mostrando últimos 30 movimientos.</p>
    </div>
</div>
@endsection