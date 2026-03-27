@extends('layouts.app', ['title' => 'Documento FEL'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Documento FEL #{{ $felDocument->id }}</h1>
                <p class="text-sm text-slate-500">
                    Tipo: {{ $felDocument->doc_type }} | Estado: {{ strtoupper($felDocument->fel_status) }}
                </p>
            </div>

            <div class="flex gap-2 flex-wrap">
                @if($felDocument->pdf)
                    <a href="/fel-documents/{{ $felDocument->id }}/pdf" target="_blank"
                       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                        PDF
                    </a>
                @endif

                @if($felDocument->xml)
                    <a href="/fel-documents/{{ $felDocument->id }}/xml" target="_blank"
                       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                        XML
                    </a>
                @endif

                @if($felDocument->html)
                    <a href="/fel-documents/{{ $felDocument->id }}/html" target="_blank"
                       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                        HTML
                    </a>
                @endif

                @if($felDocument->fel_status === 'certified')
                <form method="POST" action="/fel-documents/{{ $felDocument->id }}/cancel" class="mt-3 grid gap-3">
                    @csrf

                    <label class="text-sm">
                        <span class="block mb-1 text-slate-600">Motivo de anulación</span>
                        <input type="text" name="reason"
                            class="w-full border border-slate-300 rounded-xl px-3 py-2"
                            placeholder="Ej. Error en datos del receptor" required>
                    </label>

                    <div>
                        <button type="submit"
                            class="rounded-xl bg-rose-600 text-white px-4 py-2 text-sm font-medium hover:bg-rose-700 transition">
                            Anular FEL
                        </button>
                    </div>
                </form>
            @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">UUID</div>
            <div class="text-sm font-semibold mt-2 break-all">{{ $felDocument->uuid ?? '—' }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Serie</div>
            <div class="text-2xl font-bold mt-2">{{ $felDocument->series ?? '—' }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Número</div>
            <div class="text-2xl font-bold mt-2">{{ $felDocument->number ?? '—' }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$felDocument->total_amount_q, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 md:gap-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Datos del documento</h2>

            <div class="grid gap-3 text-sm">
                <div><span class="font-medium">Venta:</span> #{{ $felDocument->sale_id ?? '—' }}</div>
                <div><span class="font-medium">Cliente:</span> {{ $felDocument->receiver_name ?? '—' }}</div>
                <div><span class="font-medium">NIT receptor:</span> {{ $felDocument->receiver_taxid ?? '—' }}</div>
                <div><span class="font-medium">Ambiente:</span> {{ strtoupper($felDocument->environment) }}</div>
                <div><span class="font-medium">Emitido:</span> {{ $felDocument->issued_at ?? '—' }}</div>
                <div><span class="font-medium">Creado por:</span> {{ $felDocument->creator?->name ?? '—' }}</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Venta relacionada</h2>

            @if($felDocument->sale)
                <div class="grid gap-3 text-sm">
                    <div><span class="font-medium">Fecha venta:</span> {{ $felDocument->sale->sold_at }}</div>
                    <div><span class="font-medium">Combustible:</span> {{ $felDocument->sale->fuel?->name ?? '—' }}</div>
                    <div><span class="font-medium">Galones:</span> {{ number_format((float)$felDocument->sale->gallons, 3) }}</div>
                    <div><span class="font-medium">Precio por galón:</span> Q{{ number_format((float)$felDocument->sale->price_per_gallon, 2) }}</div>
                    <div><span class="font-medium">Usuario:</span> {{ $felDocument->sale->user?->name ?? '—' }}</div>
                </div>
            @else
                <div class="text-sm text-slate-500">No hay venta relacionada.</div>
            @endif
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Eventos FEL</h2>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[900px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Tipo</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($felDocument->events->sortByDesc('created_at') as $event)
                        <tr>
                            <td class="px-4 py-3">{{ $event->created_at }}</td>
                            <td class="px-4 py-3">{{ $event->event_type }}</td>
                            <td class="px-4 py-3">{{ $event->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-slate-500">No hay eventos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($felDocument->error_message)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-rose-700 mb-2">Error registrado</h2>
            <div class="text-sm text-rose-700">{{ $felDocument->error_message }}</div>
        </div>
    @endif
</div>
@endsection