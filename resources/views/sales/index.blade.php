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

<div id="felModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Emitir FEL</h2>
            <button type="button" id="btn_close_fel_modal" class="text-slate-500 hover:text-slate-700">
                ✕
            </button>
        </div>

        <div class="space-y-4 px-4 py-4">
            <input type="hidden" id="fel_sale_id">

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tipo de receptor</label>
                <select id="fel_receiver_type" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                    <option value="CF">Consumidor Final</option>
                    <option value="NIT">NIT</option>
                    <option value="CUI">CUI</option>
                </select>
            </div>

            <div id="fel_taxid_wrapper" class="hidden">
                <label class="mb-1 block text-sm font-medium text-slate-700">NIT / CUI</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="fel_taxid"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        placeholder="Ingrese NIT o CUI"
                    >
                    <button
                        type="button"
                        id="btn_lookup_nit"
                        class="hidden rounded-lg bg-blue-600 px-3 py-2 text-white hover:bg-blue-700"
                    >
                        Buscar
                    </button>
                </div>
            </div>

            <div id="fel_name_wrapper" class="hidden">
                <label class="mb-1 block text-sm font-medium text-slate-700">Nombre receptor</label>
                <input
                    type="text"
                    id="fel_receiver_name"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                    placeholder="Nombre del receptor FEL"
                >
            </div>

            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                Se emitirá FEL sobre la venta ya registrada, sin duplicarla.
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t px-4 py-3">
            <button
                type="button"
                id="btn_cancel_fel_modal"
                class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50"
            >
                Cancelar
            </button>

            <button
                type="button"
                id="btn_confirm_fel"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
            >
                Emitir FEL
            </button>
        </div>
    </div>
</div>

<script>
const out = document.getElementById('out');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const felModal = document.getElementById('felModal');
const felSaleId = document.getElementById('fel_sale_id');
const felReceiverType = document.getElementById('fel_receiver_type');
const felTaxidWrapper = document.getElementById('fel_taxid_wrapper');
const felTaxid = document.getElementById('fel_taxid');
const felNameWrapper = document.getElementById('fel_name_wrapper');
const felReceiverName = document.getElementById('fel_receiver_name');
const btnLookupNit = document.getElementById('btn_lookup_nit');
const btnConfirmFel = document.getElementById('btn_confirm_fel');
const btnCloseFelModal = document.getElementById('btn_close_fel_modal');
const btnCancelFelModal = document.getElementById('btn_cancel_fel_modal');

async function loadSales() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;

  const res = await fetch(`/api/sales-list?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try {
    data = JSON.parse(text);
  } catch {
    data = null;
  }

  if (!res.ok || !data) {
    out.innerHTML = '<span class="text-red-600">Error cargando ventas</span>';
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
                            <a href="/fel-documents/${s.fel_id}" class="underline text-slate-700">Ver</a>
                            <a href="/fel-documents/${s.fel_id}/pdf" target="_blank" class="underline text-indigo-600">PDF</a>
                            <a href="/fel-documents/${s.fel_id}/xml" target="_blank" class="underline text-blue-600">XML</a>
                            <a href="/fel-documents/${s.fel_id}/html" target="_blank" class="underline text-amber-600">HTML</a>
                          </div>
                        </div>
                      `
                      : s.fel_status === 'error'
                        ? `
                          <div class="flex flex-col gap-1">
                            <span class="inline-flex rounded-full bg-rose-100 text-rose-700 px-2 py-1 text-xs font-medium w-fit">
                              Error
                            </span>

                            <div class="text-[11px] text-rose-700 break-words">
                              ${s.fel_error_message ?? 'Error al certificar'}
                            </div>

                            ${
                              s.status === 'active'
                                ? `
                                  <button
                                    type="button"
                                    onclick="openFelModal(${s.id})"
                                    class="rounded-xl bg-indigo-600 text-white px-3 py-2 text-sm font-medium hover:bg-indigo-700 transition mt-1">
                                    Reintentar
                                  </button>
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
                              <button
                                type="button"
                                onclick="openFelModal(${s.id})"
                                class="rounded-xl bg-indigo-600 text-white px-3 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                                Emitir FEL
                              </button>
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
  try { data = JSON.parse(text); } catch { data = null; }

  if (!res.ok) {
    alert(data?.message ?? 'Error al anular FEL');
    return;
  }

  alert(data?.message ?? 'Documento FEL anulado correctamente');
  loadSales();
}

function openFelModal(saleId) {
  felSaleId.value = saleId;
  felReceiverType.value = 'CF';
  felTaxid.value = '';
  felReceiverName.value = '';
  syncFelUI();

  felModal.classList.remove('hidden');
  felModal.classList.add('flex');
}

function closeFelModal() {
  felModal.classList.add('hidden');
  felModal.classList.remove('flex');
}

function syncFelUI() {
  const type = felReceiverType.value;

  if (type === 'CF') {
    felTaxidWrapper.classList.add('hidden');
    felNameWrapper.classList.add('hidden');
    btnLookupNit.classList.add('hidden');
    felTaxid.value = '';
    felReceiverName.value = '';
    return;
  }

  felTaxidWrapper.classList.remove('hidden');
  felNameWrapper.classList.remove('hidden');

  if (type === 'NIT') {
    btnLookupNit.classList.remove('hidden');
  } else {
    btnLookupNit.classList.add('hidden');
  }
}

async function emitFelFromModal() {
  const saleId = felSaleId.value;
  const receiverType = felReceiverType.value;
  const taxid = felTaxid.value.trim();
  const receiverName = felReceiverName.value.trim();

  if (!saleId) {
    alert('No se encontró la venta para emitir FEL.');
    return;
  }

  if (receiverType !== 'CF' && taxid === '') {
    alert('Debes ingresar un NIT o CUI.');
    return;
  }

  if ((receiverType === 'NIT' || receiverType === 'CUI') && receiverName === '') {
    alert('Debes ingresar el nombre del receptor FEL.');
    return;
  }

  const originalText = btnConfirmFel.textContent;
  btnConfirmFel.textContent = 'Emitiendo...';
  btnConfirmFel.disabled = true;

  try {
    const res = await fetch(`/sales/${saleId}/fel`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        fel_receiver_type: receiverType,
        fel_taxid: taxid,
        fel_receiver_name: receiverName
      })
    });

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch {
      data = null;
    }

    if (!res.ok) {
      alert(data?.message ?? 'Error al emitir FEL');
      return;
    }

    closeFelModal();
    alert(data?.message ?? `Factura FEL emitida correctamente para la venta #${saleId}`);
    window.open(`/sales/${saleId}/ticket`, '_blank');
    loadSales();

  } catch (e) {
    alert('Error de conexión: ' + e.message);
  } finally {
    btnConfirmFel.textContent = originalText;
    btnConfirmFel.disabled = false;
  }
}

btnLookupNit.addEventListener('click', async () => {
  const nit = felTaxid.value.trim();

  if (!nit) {
    alert('Ingresa un NIT.');
    return;
  }

  const originalText = btnLookupNit.textContent;
  btnLookupNit.textContent = 'Buscando...';
  btnLookupNit.disabled = true;

  try {
    const res = await fetch('/sales/fel/lookup-nit', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ nit })
    });

    const data = await res.json();

    if (!res.ok) {
      alert(data.message ?? 'Error al consultar NIT');
      return;
    }

    if (data.data?.name) {
      felReceiverName.value = data.data.name;
    } else {
      alert('NIT válido pero sin nombre retornado.');
    }
  } catch (e) {
    alert('Error de conexión: ' + e.message);
  } finally {
    btnLookupNit.textContent = originalText;
    btnLookupNit.disabled = false;
  }
});

btnConfirmFel.addEventListener('click', emitFelFromModal);
btnCloseFelModal.addEventListener('click', closeFelModal);
btnCancelFelModal.addEventListener('click', closeFelModal);
felReceiverType.addEventListener('change', syncFelUI);

document.getElementById('btn_load').addEventListener('click', loadSales);

syncFelUI();
loadSales();
</script>
@endsection