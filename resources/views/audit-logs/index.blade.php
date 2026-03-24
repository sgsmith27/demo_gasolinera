@extends('layouts.app', ['title' => 'Bitácora de anulaciones'])

@section('content')
<div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Bitácora de anulaciones</h1>
            <p class="text-sm text-slate-500">Eventos de anulación del sistema.</p>
        </div>
    </div>

    <form method="GET" action="/audit-logs"
      class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end mb-5">

    <label class="text-sm">
        <span class="block mb-1 text-slate-600">Desde</span>
        <input type="date" name="from" value="{{ $from }}"
               class="w-full border border-slate-300 rounded-xl px-3 py-2">
    </label>

    <label class="text-sm">
        <span class="block mb-1 text-slate-600">Hasta</span>
        <input type="date" name="to" value="{{ $to }}"
               class="w-full border border-slate-300 rounded-xl px-3 py-2">
    </label>

    <label class="text-sm">
        <span class="block mb-1 text-slate-600">Módulo</span>
        <select name="module"
                class="w-full border border-slate-300 rounded-xl px-3 py-2">
            <option value="">Todos</option>
            @foreach($modules as $key => $label)
                <option value="{{ $key }}" @selected($module === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm">
        <span class="block mb-1 text-slate-600">Acción</span>
        <select name="action"
                class="w-full border border-slate-300 rounded-xl px-3 py-2">
            <option value="">Todas</option>
            @foreach($actions as $key => $label)
                <option value="{{ $key }}" @selected($action === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="text-sm">
        <span class="block mb-1 text-slate-600">Usuario</span>
        <select name="user_id"
                class="w-full border border-slate-300 rounded-xl px-3 py-2">
            <option value="">Todos</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}"
                        @selected((string)$userId === (string)$user->id)>
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

        <a href="/audit-logs/pdf?from={{ $from }}&to={{ $to }}&module={{ $module }}&action={{ $action }}&user_id={{ $userId }}"
           target="_blank"
           class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
            PDF
        </a>

        <a href="/audit-logs"
        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
        Limpiar
        </a>
    </div>

</form>


    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-[1100px] w-full text-left text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Usuario</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Módulo</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Acción</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Entidad</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">ID</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Descripción</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">{{ $log->event_at }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $modules[$log->module] ?? $log->module }}</td>
                        <td class="px-4 py-3">{{ $actions[$log->action] ?? $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->entity_type }}</td>
                        <td class="px-4 py-3">{{ $log->entity_id }}</td>
                        <td class="px-4 py-3">{{ $log->description }}</td>
                        <td class="px-4 py-3">
                            <button onclick='showMeta(@json($log->meta))'
                                class="text-sm text-blue-600 hover:underline">
                                Ver detalle
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-slate-500">No hay anulaciones registradas en el rango indicado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="metaModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Detalle del evento</h3>
            <button onclick="closeMeta()" class="text-gray-500">✕</button>
        </div>

        <pre id="metaContent" class="text-sm bg-gray-100 p-4 rounded-lg overflow-auto max-h-[400px]"></pre>
    </div>
</div>

<script>
function showMeta(meta) {
    const modal = document.getElementById('metaModal');
    const content = document.getElementById('metaContent');

    content.textContent = JSON.stringify(meta, null, 2);
    modal.classList.remove('hidden');
}

function closeMeta() {
    document.getElementById('metaModal').classList.add('hidden');
}
</script>
@endsection