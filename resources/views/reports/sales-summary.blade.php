@extends('layouts.app', ['title' => 'Resumen por rango'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Resumen por rango de fecha</h1>

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

async function loadReport() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;

  const res = await fetch(`/api/reports/sales-summary?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try {
    data = JSON.parse(text);
  } catch {
    out.innerHTML = '<span class="text-red-600">Error: respuesta no JSON</span>';
    return;
  }

  if (!res.ok) {
    out.innerHTML = `<span class="text-red-600">Error:</span> ${JSON.stringify(data)}`;
    return;
  }

  document.getElementById('btn_pdf').href = `/reports/sales-summary/pdf?from=${from}&to=${to}`;

  let html = `
    <div class="mb-4">
      <div class="font-medium">Totales</div>
      <div>Ventas: ${data.totals.total_sales} — Q${Number(data.totals.total_q).toFixed(2)} — ${Number(data.totals.total_gallons).toFixed(3)} gal</div>
    </div>
  `;

  function table(title, rows, cols) {
    return `<div class="mb-5">
      <div class="font-medium mb-2">${title}</div>
      <div class="overflow-auto border rounded-lg">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-100">
          <tr>${cols.map(c => `<th class="px-3 py-2">${c}</th>`).join('')}</tr>
        </thead>
        <tbody>
          ${rows.map(r => `<tr class="border-t">
            ${cols.map(c => `<td class="px-3 py-2">${r[c]}</td>`).join('')}
          </tr>`).join('')}
        </tbody>
      </table>
      </div>
    </div>`;
  }

  const byPump = data.by_pump.map(r => ({
    pump_code: r.pump_code,
    sales_count: r.sales_count,
    total_q: Number(r.total_q).toFixed(2),
    total_gallons: Number(r.total_gallons).toFixed(3),
  }));

  const byUser = data.by_user.map(r => ({
    user_name: r.user_name,
    sales_count: r.sales_count,
    total_q: Number(r.total_q).toFixed(2),
    total_gallons: Number(r.total_gallons).toFixed(3),
  }));

  const byFuel = data.by_fuel.map(r => ({
    fuel_name: r.fuel_name,
    sales_count: r.sales_count,
    total_q: Number(r.total_q).toFixed(2),
    total_gallons: Number(r.total_gallons).toFixed(3),
  }));

  html += table('Por bomba', byPump, ['pump_code','sales_count','total_q','total_gallons']);
  html += table('Por despachador', byUser, ['user_name','sales_count','total_q','total_gallons']);
  html += table('Por combustible', byFuel, ['fuel_name','sales_count','total_q','total_gallons']);

  out.innerHTML = html;
}

document.getElementById('btn_load').addEventListener('click', loadReport);
loadReport();
</script>
@endsection