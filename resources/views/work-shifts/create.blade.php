@extends('layouts.app', ['title' => 'Abrir turno'])

@section('content')
<div class="max-w-2xl rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
    <h1 class="text-xl font-semibold text-slate-800 mb-4">Abrir turno</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/work-shifts" class="grid gap-4">
        @csrf

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Despachador</span>
            <select name="user_id" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Fecha y hora de inicio</span>
            <input type="datetime-local" name="started_at" value="{{ old('started_at', $now) }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
        <span class="block mb-1 text-slate-600">Asignación de trabajo</span>
        <select name="assignment_mode" id="assignment_mode"
                class="w-full border border-slate-300 rounded-xl px-3 py-2">
            <option value="free" @selected(old('assignment_mode') === 'free')>Libre (varias bombas)</option>
            <option value="fixed" @selected(old('assignment_mode') === 'fixed')>Bomba fija</option>
        </select>
    </label>

    <label class="text-sm" id="pump_wrap">
        <span class="block mb-1 text-slate-600">Bomba asignada</span>
        <select name="pump_id" class="w-full border border-slate-300 rounded-xl px-3 py-2">
            <option value="">Selecciona una bomba</option>
            @foreach($pumps as $pump)
                <option value="{{ $pump->id }}" @selected(old('pump_id') == $pump->id)>
                    {{ $pump->code }} — {{ $pump->name ?: 'Sin nombre' }}
                </option>
            @endforeach
        </select>
    </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Fondo inicial (Q)</span>
            <input type="number" step="0.01" min="0" name="opening_cash_q" value="{{ old('opening_cash_q', '0.00') }}"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-slate-600">Observaciones de apertura</span>
            <textarea name="opening_notes" rows="4"
                      class="w-full border border-slate-300 rounded-xl px-3 py-2">{{ old('opening_notes') }}</textarea>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                Abrir turno
            </button>

            <a href="/work-shifts" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mode = document.getElementById('assignment_mode');
    const pumpWrap = document.getElementById('pump_wrap');

    function togglePump() {
        if (mode.value === 'fixed') {
            pumpWrap.classList.remove('hidden');
        } else {
            pumpWrap.classList.add('hidden');
        }
    }

    togglePump();
    mode.addEventListener('change', togglePump);
});
</script>
@endsection