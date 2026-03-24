@extends('layouts.app', ['title' => 'Abastecimientos'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Abastecimientos</h1>
        <a href="/fuel-deliveries/new" class="border rounded-lg px-4 py-2">Nuevo abastecimiento</a>
    </div>

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
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function loadDeliveries() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;

  document.getElementById('btn_pdf').href = `/fuel-deliveries/pdf?from=${from}&to=${to}`;
  const res = await fetch(`/api/fuel-deliveries-list?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch { data = null; }

  if (!res.ok || !data) {
    out.innerHTML = '<span class="text-red-600">Error cargando abastecimientos</span>';
    return;
  }

  let html = `
    <div class="overflow-auto border rounded-lg">
      <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-3 py-2">ID</th>
            <th class="px-3 py-2">Fecha</th>
            <th class="px-3 py-2">Usuario</th>
            <th class="px-3 py-2">Combustible</th>
            <th class="px-3 py-2">Tanque</th>
            <th class="px-3 py-2">Galones</th>
            <th class="px-3 py-2">Costo Q</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2">Acciones</th>
          </tr>
        </thead>
        <tbody>
          ${data.deliveries.map(d => `
            <tr class="border-t">
              <td class="px-3 py-2">${d.id}</td>
              <td class="px-3 py-2">${d.delivered_at}</td>
              <td class="px-3 py-2">${d.user_name}</td>
              <td class="px-3 py-2">${d.fuel_name}</td>
              <td class="px-3 py-2">#${d.tank_id}</td>
              <td class="px-3 py-2">${Number(d.gallons).toFixed(3)}</td>
              <td class="px-3 py-2">${d.total_cost_q === null ? '' : 'Q' + Number(d.total_cost_q).toFixed(2)}</td>
              <td class="px-3 py-2">${d.status}</td>
              <td class="px-3 py-2">
                ${d.status === 'active'
                  ? `<button class="underline text-red-600" onclick="voidDelivery(${d.id})">Anular</button>`
                  : `<span class="text-gray-500">—</span>`}
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;

  out.innerHTML = html;
}

async function voidDelivery(id) {
  const reason = prompt('Motivo de anulación:');
  if (!reason) return;

  const res = await fetch(`/fuel-deliveries/${id}/void`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ reason })
  });

  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch { data = null; }

  if (!res.ok) {
    alert(data?.message ?? 'Error al anular abastecimiento');
    return;
  }

  alert('Abastecimiento anulado correctamente');
  loadDeliveries();
}

document.getElementById('btn_load').addEventListener('click', loadDeliveries);
loadDeliveries();
</script>
@endsection