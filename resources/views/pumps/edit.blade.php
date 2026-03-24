@extends('layouts.app', ['title' => 'Editar bomba'])

@section('content')
<div class="grid gap-4">
    <div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
        <h1 class="text-xl font-semibold mb-4">Editar bomba</h1>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/pumps/{{ $pump->id }}" class="grid gap-3">
            @csrf
            @method('PUT')

            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Código</span>
                <input name="code" type="text" value="{{ old('code', $pump->code) }}" class="w-full border rounded-lg px-3 py-2">
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Nombre</span>
                <input name="name" type="text" value="{{ old('name', $pump->name) }}" class="w-full border rounded-lg px-3 py-2">
            </label>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $pump->is_active))>
                <span>Activa</span>
            </label>

            <div class="flex gap-2">
                <button type="submit" class="bg-black text-white rounded-lg px-4 py-2">
                    Guardar cambios
                </button>
                <a href="/pumps" class="border rounded-lg px-4 py-2">Volver</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="text-lg font-semibold mb-4">Mangueras</h2>

        <div class="overflow-auto border rounded-lg mb-4">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2">Código</th>
                        <th class="px-3 py-2">Combustible</th>
                        <th class="px-3 py-2">Estado</th>
                        <th class="px-3 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pump->nozzles as $nozzle)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $nozzle->code }}</td>
                            <td class="px-3 py-2">{{ $nozzle->fuel->name }}</td>                            
                            <td class="px-3 py-2">
                                @if($nozzle->is_active)
                                    <span class="inline-block bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded">Activa</span>
                                @else
                                    <span class="inline-block bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <a href="/nozzles/{{ $nozzle->id }}/edit" class="underline text-sm">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-2 text-gray-500">Sin mangueras</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3 class="text-base font-semibold mb-3">Agregar manguera</h3>

        <form method="POST" action="/pumps/{{ $pump->id }}/nozzles" class="grid gap-3 max-w-2xl">
            @csrf

            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Combustible</span>
                <select name="fuel_id" class="w-full border rounded-lg px-3 py-2">
                    @foreach($fuels as $fuel)
                        <option value="{{ $fuel->id }}">{{ $fuel->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Código</span>
                <input name="code" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Ej. B5-REG">
            </label>

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Activa</span>
            </label>

            <div>
                <button type="submit" class="bg-black text-white rounded-lg px-4 py-2">
                    Agregar manguera
                </button>
            </div>
        </form>
    </div>
</div>
@endsection