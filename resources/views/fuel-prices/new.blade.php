@extends('layouts.app', ['title' => 'Nuevo precio'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-2xl">
    <h1 class="text-xl font-semibold mb-4">Registrar nuevo precio</h1>

    <div class="grid gap-3">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Combustible</span>
            <select id="fuel_id" class="w-full border rounded-lg px-3 py-2">
                @foreach($fuels as $fuel)
                    <option value="{{ $fuel->id }}">{{ $fuel->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Precio por galón (Q)</span>
            <input id="price" type="number" step="0.0001" min="0.0001" class="w-full border rounded-lg px-3 py-2" placeholder="0.0000">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Vigente desde</span>
            <input id="valid_from" type="datetime-local" value="{{ $now }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <button id="btn_submit" class="bg-black text-white rounded-lg px-4 py-2">
            Registrar precio
        </button>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<script>
const result = document.getElementById('result');

document.getElementById('btn_submit').addEventListener('click', async () => {
  result.textContent = 'Enviando...';

  const payload = {
    fuel_id: Number(document.getElementById('fuel_id').value),
    price: Number(document.getElementById('price').value),
    valid_from: document.getElementById('valid_from').value
  };

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/fuel-prices', {
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

    result.innerHTML = `<span class="text-green-700 font-medium">OK:</span> Precio registrado correctamente`;
    document.getElementById('price').value = '';
  } catch (e) {
    result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
});
</script>
@endsection