@extends('layouts.app', ['title' => 'Abastecer combustible'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Abastecer combustible</h1>

    <div class="grid gap-3">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Fecha y hora</span>
            <input id="delivered_at" type="datetime-local" value="{{ $today }}" class="w-full border rounded-lg px-3 py-2">
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
            <span class="block mb-1 text-gray-700">Galones ingresados</span>
            <input id="gallons" type="number" step="0.001" min="0.001" class="w-full border rounded-lg px-3 py-2" placeholder="0.000">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Costo total (Q)</span>
            <input id="total_cost_q" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2" placeholder="0.00">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Notas</span>
            <textarea id="notes" class="w-full border rounded-lg px-3 py-2" rows="3" placeholder="Proveedor, factura, observaciones..."></textarea>
        </label>

        <button id="btn_submit" class="bg-black text-white rounded-lg px-4 py-2">
            Registrar abastecimiento
        </button>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<script>
const result = document.getElementById('result');

document.getElementById('btn_submit').addEventListener('click', async () => {
  result.textContent = 'Enviando...';

  const payload = {
    delivered_at: document.getElementById('delivered_at').value,
    tank_id: Number(document.getElementById('tank_id').value),
    gallons: Number(document.getElementById('gallons').value),
    total_cost_q: document.getElementById('total_cost_q').value === '' ? null : Number(document.getElementById('total_cost_q').value),
    notes: document.getElementById('notes').value || null,
  };

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/fuel-deliveries', {
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

    result.innerHTML = `<span class="text-green-700 font-medium">OK:</span> Abastecimiento #${data.data.id} registrado`;
    document.getElementById('gallons').value = '';
    document.getElementById('total_cost_q').value = '';
    document.getElementById('notes').value = '';
  } catch (e) {
    result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
});
</script>
@endsection