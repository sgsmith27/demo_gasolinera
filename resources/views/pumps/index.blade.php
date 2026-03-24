@extends('layouts.app', ['title' => 'Bombas'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Bombas y mangueras</h1>
        <div class="flex gap-2">
            <a href="/pumps/new" class="border rounded-lg px-4 py-2">Nueva bomba</a>
            <form method="POST" action="/pumps/seed-defaults">
                @csrf
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4">
        @foreach($pumps as $pump)
            <div class="border rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-lg font-semibold">{{ $pump->code }}</div>
                        <div class="text-sm text-gray-600">{{ $pump->name ?: 'Sin nombre' }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($pump->is_active)
                            <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">
                                Activa
                            </span>
                        @else
                            <span class="inline-block bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">
                                Inactiva
                            </span>
                        @endif

                        <a href="/pumps/{{ $pump->id }}/edit" class="underline text-sm">Editar</a>
                    </div>
                </div>

                <div class="overflow-auto border rounded-lg">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2">Código</th>
                                <th class="px-3 py-2">Combustible</th>
                                <th class="px-3 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pump->nozzles as $nozzle)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $nozzle->code }}</td>
                                    <td class="px-3 py-2">{{ $nozzle->fuel->name }}</td>
                                    <td class="px-3 py-2">
                                        @if($nozzle->is_active)
                                            <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">
                                                Activa
                                            </span>
                                        @else
                                            <span class="inline-block bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-2 text-gray-500">Sin mangueras</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection