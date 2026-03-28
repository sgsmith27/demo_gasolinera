@extends('layouts.app', ['title' => 'Ventas'])

@if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if($errors->has('fel'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        {{ $errors->first('fel') }}
    </div>
@endif

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Ventas</h1>
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

        <button id="btn_load" class="bg-black text-white rounded-lg px-4 py-2">Cargar</button>
    </div>

    <div id="out" class="text-sm"></div>
</div>

<script>
const out = document.getElementById('out');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

async function loadSales() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;

  const res = await fetch(`/api/sales-list?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try { data = JSON.parse(text); } catch { data = null; }

  if (!res.ok || !data) {
    out.innerHTML = '<span class="text-red-600">Error cargando ventas</span>';
    return;
  }
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let html = `
  <div class="overflow-auto border rounded-lg">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-3 py-2">ID</th>
          <th class="px-3 py-2">Fecha</th>
          <th class="px-3 py-2">Usuario</th>
          <th class="px-3 py-2">Combustible</th>
          <th class="px-3 py-2">Galones</th>
          <th class="px-3 py-2">Total Q</th>
          <th class="px-3 py-2">Estado</th>
          <th class="px-3 py-2">Acciones</th>
          <th class="px-3 py-2">FEL</th>
        </tr>
      </thead>
      <tbody>
        ${data.sales.map(s => `
          <tr class="border-t">
            <td class="px-3 py-2">${s.id}</td>
            <td class="px-3 py-2">${s.sold_at}</td>
            <td class="px-3 py-2">${s.user_name}</td>
            <td class="px-3 py-2">${s.fuel_name}</td>
            <td class="px-3 py-2">${Number(s.gallons).toFixed(3)}</td>
            <td class="px-3 py-2">Q${Number(s.total_amount_q).toFixed(2)}</td>
            <td class="px-3 py-2">${s.status}</td>
            <td class="px-3 py-2">
            <div class="flex flex-wrap items-center gap-2">
              <a
                href="/sales/${s.id}/ticket"
                target="_blank"
                class="underline text-emerald-600"
              >
                Ticket
              </a>

              ${s.status === 'active'
                ? `<button class="underline text-red-600" onclick="voidSale(${s.id})">Anular</button>`
                : ``}
            </div>
          </td>

            <td class="px-3 py-2">
              ${
                s.fel_status === 'certified'
                ? `
                <div class="flex flex-col gap-1">
                  <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-2 py-1 text-xs font-medium w-fit">
                    Certificada
                  </span>

                  <div class="text-[11px] text-gray-500 break-all">
                    ${s.fel_uuid ?? ''}
                  </div>

                  <div class="flex flex-wrap gap-2 mt-1">
                    <a href="/fel-documents/${s.fel_id}" class="underline text-slate-700">Ver</a>
                    <a href="/fel-documents/${s.fel_id}/pdf" target="_blank" class="underline text-indigo-600">PDF</a>
                    <a href="/fel-documents/${s.fel_id}/xml" target="_blank" class="underline text-blue-600">XML</a>
                    <a href="/fel-documents/${s.fel_id}/html" target="_blank" class="underline text-amber-600">HTML</a>
                  </div>

                  <div class="mt-1">
                    <button
                      type="button"
                      onclick="cancelFel(${s.fel_id})"
                      class="underline text-red-600"
                    >
                      Anular FEL
                    </button>
                  </div>
                </div>
                  `
                  : s.fel_status === 'error'
                    ? `
                      <div class="flex flex-col gap-1">
                        <span class="inline-flex rounded-full bg-rose-100 text-rose-700 px-2 py-1 text-xs font-medium w-fit">
                          Error
                        </span>
                        ${
                          s.status === 'active'
                            ? `
                              <form method="POST" action="/sales/${s.id}/fel" class="inline mt-1">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit"
                                  class="rounded-xl bg-indigo-600 text-white px-3 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                                  Reintentar
                                </button>
                              </form>
                            `
                            : `<span class="text-gray-500">—</span>`
                        }
                      </div>
                    `
                    : s.fel_status === 'pending'
                      ? `
                        <span class="inline-flex rounded-full bg-amber-100 text-amber-700 px-2 py-1 text-xs font-medium">
                          Pendiente
                        </span>
                      `
                      : s.status === 'active'
                        ? `
                          <form method="POST" action="/sales/${s.id}/fel" class="inline">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit"
                              class="rounded-xl bg-indigo-600 text-white px-3 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                              Emitir FEL
                            </button>
                          </form>
                        `
                        : s.fel_status === 'cancelled'
                        ? `
                          <div class="flex flex-col gap-1">
                            <span class="inline-flex rounded-full bg-slate-200 text-slate-700 px-2 py-1 text-xs font-medium w-fit">
                              Anulada
                            </span>
                            <div class="text-[11px] text-gray-500 break-all">
                              ${s.fel_uuid ?? ''}
                            </div>
                            <div class="flex flex-wrap gap-2 mt-1">
                              <a href="/fel-documents/${s.fel_id}"
                                class="underline text-slate-700">
                                Ver
                              </a>
                            </div>
                          </div>
                        `
                        : `<span class="text-gray-500">—</span>`
                        
              }
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  </div>
`;

out.innerHTML = html;
}

async function voidSale(id) {
  const reason = prompt('Motivo de anulación:');
  if (!reason) return;

  const res = await fetch(`/sales/${id}/void`, {
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
    alert(data?.message ?? 'Error al anular venta');
    return;
  }

  alert('Venta anulada correctamente');
  loadSales();
}

async function cancelFel(felId) {
  const reason = prompt('Motivo de anulación FEL:');
  if (!reason) return;

  const res = await fetch(`/fel-documents/${felId}/cancel`, {
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
  try {
    data = JSON.parse(text);
  } catch {
    data = null;
  }

  if (!res.ok) {
    alert(data?.message ?? 'Error al anular FEL');
    return;
  }

  alert(data?.message ?? 'Documento FEL anulado correctamente');
  loadSales();
}

document.getElementById('btn_load').addEventListener('click', loadSales);
loadSales();
</script>
@endsection