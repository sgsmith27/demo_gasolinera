@extends('layouts.app', ['title' => 'Turnos'])

@section('content')
<div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Turnos</h1>
            <p class="text-sm text-slate-500">Listado de turnos registrados.</p>
        </div>

        <a href="/work-shifts/open" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
            Abrir turno
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-[900px] w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-slate-600">ID</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Despachador</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Inicio</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Fin</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Fondo inicial</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Asignación</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $shift->id }}</td>
                        <td class="px-4 py-3">{{ $shift->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $shift->started_at }}</td>
                        <td class="px-4 py-3">{{ $shift->ended_at ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($shift->status === 'open')
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-2 py-1 text-xs font-medium">Abierto</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs font-medium">Cerrado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">Q{{ number_format((float)$shift->opening_cash_q, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($shift->assignment_mode === 'fixed')
                                <span class="inline-flex rounded-full bg-indigo-100 text-indigo-700 px-2 py-1 text-xs font-medium">
                                    {{ $shift->pump?->code ?? 'Bomba fija' }}
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 text-slate-700 px-2 py-1 text-xs font-medium">
                                    Libre
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="/work-shifts/{{ $shift->id }}" class="text-indigo-600 hover:underline">Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-slate-500">No hay turnos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection