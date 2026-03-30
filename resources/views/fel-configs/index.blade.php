@extends('layouts.app', ['title' => 'Configuración FEL'])

@section('content')
<div class="space-y-4">

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Configuraciones FEL</h1>

        <a
            href="{{ route('fel-configs.create') }}"
            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        >
            Nueva configuración
        </a>
    </div>

    <div class="overflow-auto rounded-xl border bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Ambiente</th>
                    <th class="px-4 py-3 text-left">Tax ID</th>
                    <th class="px-4 py-3 text-left">Usuario</th>
                    <th class="px-4 py-3 text-left">Nombre emisor</th>
                    <th class="px-4 py-3 text-left">Afiliación IVA</th>
                    <th class="px-4 py-3 text-left">Personería</th>
                    <th class="px-4 py-3 text-left">Estado</th>
                    <th class="px-4 py-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($configs as $config)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $config->id }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $config->environment === 'production' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-sky-700' }}">
                                {{ strtoupper($config->environment) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $config->taxid }}</td>
                        <td class="px-4 py-3">{{ $config->username }}</td>
                        <td class="px-4 py-3">{{ $config->seller_name }}</td>
                        <td class="px-4 py-3">{{ $config->afiliacion_iva }}</td>
                        <td class="px-4 py-3">{{ $config->tipo_personeria }}</td>
                        <td class="px-4 py-3">
                            @if($config->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">
                                    Activa
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                    Inactiva
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(!$config->is_active)
                                <form method="POST" action="{{ route('fel-configs.activate', $config) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700"
                                    >
                                        Activar
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                            No hay configuraciones FEL registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection