@extends('layouts.app', ['title' => 'Precios de combustible'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Precios de combustible</h1>
        <a href="/fuel-prices/new" class="border rounded-lg px-4 py-2">Nuevo precio</a>
    </div>

    <div id="out" class="text-sm">Cargando...</div>
</div>

<script>
const out = document.getElementById('out');

async function loadPrices() {
  out.textContent = 'Cargando...';

  try {
    const fuelsRes = await fetch('/fuels-with-prices', {
      headers: { 'Accept': 'application/json' }
    });

    const text = await fuelsRes.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch {
      out.innerHTML = `<span class="text-red-600">Error:</span> respuesta no JSON`;
      return;
    }

    if (!fuelsRes.ok) {
      out.innerHTML = `<span class="text-red-600">Error:</span> ${JSON.stringify(data)}`;
      return;
    }

    let html = `<div class="mb-4 text-sm text-gray-600">Mostrando historial del mes actual: ${data.month}</div>`;

    for (const fuel of data.fuels) {
      html += `
        <div class="border rounded-xl p-4 mb-5">
          <div class="text-lg font-semibold mb-2">${fuel.name}</div>
          <div class="mb-3">
  <div class="mb-3">
  <div>
    <span class="text-gray-600">Precio vigente:</span>
    <span class="font-semibold">Q${Number(fuel.current_price_per_gallon ?? 0).toFixed(2)} / gal</span>
  </div>
  <div class="text-xs text-gray-500">
    Vigente desde: ${fuel.current_valid_from ?? '—'}
  </div>
</div>

          <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2">Vigente desde</th>
                  <th class="px-3 py-2">Precio por galón</th>
                </tr>
              </thead>
              <tbody>
                ${fuel.price_history.length
                  ? fuel.price_history.map(row => `
                    <tr class="border-t">
                      <td class="px-3 py-2">${row.valid_from}</td>
                      <td class="px-3 py-2">Q${Number(row.price_per_gallon).toFixed(4)}</td>
                    </tr>
                  `).join('')
                  :`<tr><td colspan="2" class="px-3 py-2 text-gray-500">Sin cambios registrados en el mes actual</td></tr>`
                }
              </tbody>
            </table>
          </div>
        </div>
      `;
    }

    out.innerHTML = html;
  } catch (e) {
    out.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
}

loadPrices();
</script>
@endsection