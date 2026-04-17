@extends('layouts.app', ['title' => 'Cierre diario'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Cierre diario</h1>

    <div class="flex gap-2 items-end mb-4">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Fecha</span>
            <input id="date" type="date" value="{{ $today }}" class="border rounded-lg px-3 py-2">
        </label>
        <button id="btn_load" class="bg-black text-white rounded-lg px-4 py-2">Cargar</button>
    </div>

    <div id="out" class="text-sm"></div>
</div>

<script>
const out = document.getElementById('out');

async function load() {
  out.textContent = 'Cargando...';
  const date = document.getElementById('date').value;

  const res = await fetch(`/api/reports/daily-close?date=${date}`);
  const data = await res.json();

  if (!res.ok) {
    out.innerHTML = `<span class="text-red-600">Error:</span> ${JSON.stringify(data)}`;
    return;
  }

  const totals = data.totals;
  let html = `
    <div class="mb-4">
      <div class="font-medium">Totales</div>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
  Ventas: ${totals.total_sales} — 
  Q${Number(totals.total_q).toFixed(2)} — 
  ${Number(totals.total_gallons).toFixed(3)} gal
</div>
    </div>
  `;
  const labels = {
  pump_code: 'Bomba',
  sales_count: 'Ventas',
  total_q: 'Total (Q)',
  total_gallons: 'Galones',
  user_name: 'Despachador',
  fuel_name: 'Combustible',
  };

  function table(title, rows, cols) {
    let t = `<div class="mb-5">
      <div class="font-medium mb-2">${title}</div>
      <div class="overflow-auto border rounded-lg">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-100">
          <tr>
            ${cols.map(c => `<th class="px-3 py-2">${labels[c] ?? c}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `<tr class="border-t">
            ${cols.map(c => {
  let value = r[c];

  if (c === 'total_q') {
    value = 'Q' + Number(value).toFixed(2);
  }

  if (c === 'total_gallons') {
    value = Number(value).toFixed(3) + ' gal';
  }

  return `<td class="px-3 py-2">${value}</td>`;
}).join('')}
          </tr>`).join('')}
        </tbody>
      </table>
      </div>
    </div>`;
    return t;
  }

  // Normalizamos keys que usaremos como columnas
  const byPump = data.by_pump.map(r => ({
    pump_code: r.pump_code,
    sales_count: r.sales_count,
    total_q: r.total_q,
    total_gallons: r.total_gallons,
  }));

  const byUser = data.by_user.map(r => ({
    user_name: r.user_name,
    sales_count: r.sales_count,
    total_q: r.total_q,
    total_gallons: r.total_gallons,
  }));

  const byFuel = data.by_fuel.map(r => ({
    fuel_name: r.fuel_name,
    sales_count: r.sales_count,
    total_q: r.total_q,
    total_gallons: r.total_gallons,
  }));

  html += table('Por bomba', byPump, ['pump_code','sales_count','total_q','total_gallons']);
  html += table('Por despachador', byUser, ['user_name','sales_count','total_q','total_gallons']);
  html += table('Por combustible', byFuel, ['fuel_name','sales_count','total_q','total_gallons']);

  out.innerHTML = html;
}

document.getElementById('btn_load').addEventListener('click', load);
load();
</script>
@endsection