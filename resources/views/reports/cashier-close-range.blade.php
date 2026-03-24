@extends('layouts.app', ['title' => 'Cuadre despachador por rango'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Cuadre despachador por rango</h1>

    <div class="grid md:grid-cols-3 gap-3 items-end mb-4">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Desde</span>
            <input id="from" type="date" value="{{ $today }}" class="border rounded-lg px-3 py-2 w-full">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Hasta</span>
            <input id="to" type="date" value="{{ $today }}" class="border rounded-lg px-3 py-2 w-full">
        </label>

        <div class="flex gap-2">
            <button id="btn_load" class="bg-black text-white rounded-lg px-4 py-2">Cargar</button>
            <a id="btn_pdf" href="#" target="_blank" class="border rounded-lg px-4 py-2">PDF</a>
        </div>
    </div>

    <div id="out" class="text-sm"></div>
</div>

<script>
const out = document.getElementById('out');

function money(v) { return `Q${Number(v).toFixed(2)}`; }
function gallons(v) { return `${Number(v).toFixed(3)} gal`; }

async function loadReport() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;

  const res = await fetch(`/api/reports/cashier-close-range?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try {
    data = JSON.parse(text);
  } catch {
    out.innerHTML = `<span class="text-red-600">Error:</span> respuesta no JSON`;
    return;
  }

  if (!res.ok) {
    out.innerHTML = `<span class="text-red-600">Error:</span> ${JSON.stringify(data)}`;
    return;
  }

  document.getElementById('btn_pdf').href = `/reports/cashier-close-range/pdf?from=${from}&to=${to}`;

  if (!data.summary_by_user.length) {
    out.innerHTML = `<div class="text-gray-600">No hay ventas para el rango seleccionado.</div>`;
    return;
  }

  let html = '';

  for (const user of data.summary_by_user) {
    const payments = data.payments_by_user.filter(p => Number(p.user_id) === Number(user.user_id));
    const fuels = data.fuels_by_user.filter(f => Number(f.user_id) === Number(user.user_id));

    html += `
      <div class="border rounded-xl p-4 mb-5">
        <div class="text-lg font-semibold mb-2">${user.user_name}</div>

        <div class="grid md:grid-cols-3 gap-3 mb-4">
          <div class="bg-gray-50 border rounded-lg p-3">
            <div class="text-gray-600 text-xs">Ventas</div>
            <div class="font-semibold">${user.sales_count}</div>
          </div>
          <div class="bg-gray-50 border rounded-lg p-3">
            <div class="text-gray-600 text-xs">Total vendido</div>
            <div class="font-semibold">${money(user.total_q)}</div>
          </div>
          <div class="bg-gray-50 border rounded-lg p-3">
            <div class="text-gray-600 text-xs">Galones vendidos</div>
            <div class="font-semibold">${gallons(user.total_gallons)}</div>
          </div>
        </div>

        <div class="mb-4">
          <div class="font-medium mb-2">Métodos de pago</div>
          <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2">Método</th>
                  <th class="px-3 py-2">Ventas</th>
                  <th class="px-3 py-2">Total Q</th>
                  <th class="px-3 py-2">Galones</th>
                </tr>
              </thead>
              <tbody>
                ${payments.map(p => `
                  <tr class="border-t">
                    <td class="px-3 py-2">${p.payment_method}</td>
                    <td class="px-3 py-2">${p.sales_count}</td>
                    <td class="px-3 py-2">${money(p.total_q)}</td>
                    <td class="px-3 py-2">${gallons(p.total_gallons)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>

        <div>
          <div class="font-medium mb-2">Por combustible</div>
          <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-left text-sm">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2">Combustible</th>
                  <th class="px-3 py-2">Ventas</th>
                  <th class="px-3 py-2">Total Q</th>
                  <th class="px-3 py-2">Galones</th>
                </tr>
              </thead>
              <tbody>
                ${fuels.map(f => `
                  <tr class="border-t">
                    <td class="px-3 py-2">${f.fuel_name}</td>
                    <td class="px-3 py-2">${f.sales_count}</td>
                    <td class="px-3 py-2">${money(f.total_q)}</td>
                    <td class="px-3 py-2">${gallons(f.total_gallons)}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    `;
  }

  out.innerHTML = html;
}

document.getElementById('btn_load').addEventListener('click', loadReport);
loadReport();
</script>
@endsection