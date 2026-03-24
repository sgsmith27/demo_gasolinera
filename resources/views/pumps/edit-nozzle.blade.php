@extends('layouts.app', ['title' => 'Editar manguera'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Editar manguera</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 text-sm text-gray-600">
        Bomba: <span class="font-medium">{{ $nozzle->pump->code }}</span>
        @if($nozzle->pump->name)
            — {{ $nozzle->pump->name }}
        @endif
    </div>

    <form method="POST" action="/nozzles/{{ $nozzle->id }}" class="grid gap-3">
        @csrf
        @method('PUT')

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Combustible</span>
            <select name="fuel_id" class="w-full border rounded-lg px-3 py-2">
                @foreach($fuels as $fuel)
                    <option value="{{ $fuel->id }}" @selected(old('fuel_id', $nozzle->fuel_id) == $fuel->id)>
                        {{ $fuel->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Código</span>
            <input name="code" type="text" value="{{ old('code', $nozzle->code) }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $nozzle->is_active))>
            <span>Activa</span>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="bg-black text-white rounded-lg px-4 py-2">
                Guardar cambios
            </button>
            <a href="/pumps/{{ $nozzle->pump_id }}/edit" class="border rounded-lg px-4 py-2">
                Volver
            </a>
        </div>
    </form>
</div>
@endsection