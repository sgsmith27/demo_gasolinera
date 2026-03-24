@extends('layouts.app', ['title' => 'Reporte de turnos'])

@section('content')
<div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Reporte de turnos</h1>
            <p class="text-sm text-slate-500">Consulta turnos por fecha o rango.</p>
        </div>
    </div>

    <form method="GET" action="/work-shifts-report" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end mb-5">
        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Desde</span>
            <input type="date" name="from" value="{{ $from }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Hasta</span>
            <input type="date" name="to" value="{{ $to }}" class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Despachador</span>
            <select name="user_id" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                <option value="">Todos</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string)$userId === (string)$user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <div class="flex gap-2">
            <button type="submit"
                class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                Filtrar
            </button>

            <a href="/work-shifts-report/pdf?from={{ $from }}&to={{ $to }}&user_id={{ $userId }}"
               target="_blank"
               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                PDF
            </a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-[1100px] w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-slate-600">ID</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Despachador</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Inicio</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Fin</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Estado</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Asignación</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Fondo inicial</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Esperado</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Entregado</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Diferencia</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $shift->id }}</td>
                        <td class="px-4 py-3">{{ $shift->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $shift->started_at }}</td>
                        <td class="px-4 py-3">{{ $shift->ended_at ?? '—' }}</td>
                        <td class="px-4 py-3">{{ strtoupper($shift->status) }}</td>
                        <td class="px-4 py-3">
                            @if($shift->assignment_mode === 'fixed')
                                {{ $shift->pump?->code ?? 'Bomba fija' }}
                            @else
                                Libre
                            @endif
                        </td>
                        <td class="px-4 py-3">Q{{ number_format((float)$shift->opening_cash_q, 2) }}</td>
                        <td class="px-4 py-3">{{ $shift->expected_cash_q !== null ? 'Q'.number_format((float)$shift->expected_cash_q, 2) : '—' }}</td>
                        <td class="px-4 py-3">{{ $shift->delivered_cash_q !== null ? 'Q'.number_format((float)$shift->delivered_cash_q, 2) : '—' }}</td>
                        <td class="px-4 py-3">{{ $shift->cash_difference_q !== null ? 'Q'.number_format((float)$shift->cash_difference_q, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-slate-500">No hay turnos en el rango seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection