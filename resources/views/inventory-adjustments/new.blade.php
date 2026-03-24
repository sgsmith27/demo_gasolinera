@extends('layouts.app', ['title' => 'Ajuste manual de inventario'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Ajuste manual de inventario</h1>

    <div class="grid gap-3">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Fecha y hora</span>
            <input id="adjusted_at" type="datetime-local" value="{{ $now }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Tanque</span>
            <select id="tank_id" class="w-full border rounded-lg px-3 py-2">
                @foreach($tanks as $tank)
                    <option value="{{ $tank->id }}">
                        #{{ $tank->id }} — {{ $tank->fuel_name }}
                        @if($tank->name) — {{ $tank->name }} @endif
                        — Actual: {{ number_format((float)$tank->current_gallons, 3) }} gal
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Tipo de ajuste</span>
            <select id="adjustment_type" class="w-full border rounded-lg px-3 py-2">
                <option value="IN">Aumentar inventario</option>
                <option value="OUT">Disminuir inventario</option>
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Galones</span>
            <input id="gallons" type="number" step="0.001" min="0.001" class="w-full border rounded-lg px-3 py-2" placeholder="0.000">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Motivo</span>
            <textarea id="reason" class="w-full border rounded-lg px-3 py-2" rows="4" placeholder="Describe el motivo del ajuste..."></textarea>
        </label>

        <button id="btn_submit" class="bg-black text-white rounded-lg px-4 py-2">
            Registrar ajuste
        </button>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<script>
const result = document.getElementById('result');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

document.getElementById('btn_submit').addEventListener('click', async () => {
  result.textContent = 'Enviando...';

  const payload = {
    adjusted_at: document.getElementById('adjusted_at').value,
    tank_id: Number(document.getElementById('tank_id').value),
    adjustment_type: document.getElementById('adjustment_type').value,
    gallons: Number(document.getElementById('gallons').value),
    reason: document.getElementById('reason').value
  };

  try {
    const res = await fetch('/inventory-adjustments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    });

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch {
      data = { message: 'Respuesta no JSON', raw: text.slice(0, 300) };
    }

    if (!res.ok) {
      result.innerHTML = `<span class="text-red-600">Error:</span> ${data.message ?? 'Error'}
        <pre class="whitespace-pre-wrap">${JSON.stringify(data, null, 2)}</pre>`;
      return;
    }

    result.innerHTML = `<span class="text-green-700 font-medium">OK:</span> Ajuste #${data.data.id} registrado correctamente`;
    document.getElementById('gallons').value = '';
    document.getElementById('reason').value = '';
  } catch (e) {
    result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
});
</script>
@endsection