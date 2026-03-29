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
<div id="page_alert" class="hidden mb-4 rounded-xl border px-4 py-3 text-sm"></div>
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

<div id="reasonModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 id="reason_modal_title" class="text-lg font-semibold text-slate-800">Ingresar motivo</h2>
            <button type="button" id="btn_close_reason_modal" class="text-slate-500 hover:text-slate-700">
                ✕
            </button>
        </div>

        <div class="space-y-4 px-4 py-4">
            <input type="hidden" id="reason_action_type">
            <input type="hidden" id="reason_target_id">

            <div>
                <label for="reason_input" class="mb-1 block text-sm font-medium text-slate-700">
                    Motivo
                </label>
                <textarea
                    id="reason_input"
                    rows="4"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2"
                    placeholder="Escribe el motivo..."
                ></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t px-4 py-3">
            <button
                type="button"
                id="btn_cancel_reason_modal"
                class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50"
            >
                Cancelar
            </button>

            <button
                type="button"
                id="btn_confirm_reason_modal"
                class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
            >
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
const out = document.getElementById('out');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const pageAlert = document.getElementById('page_alert');

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

const reasonModal = document.getElementById('reasonModal');
const reasonModalTitle = document.getElementById('reason_modal_title');
const reasonActionType = document.getElementById('reason_action_type');
const reasonTargetId = document.getElementById('reason_target_id');
const reasonInput = document.getElementById('reason_input');
const btnCloseReasonModal = document.getElementById('btn_close_reason_modal');
const btnCancelReasonModal = document.getElementById('btn_cancel_reason_modal');
const btnConfirmReasonModal = document.getElementById('btn_confirm_reason_modal');

function showAlert(message, type = 'success') {
  const styles = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
    error: 'border-rose-200 bg-rose-50 text-rose-700',
    warning: 'border-amber-200 bg-amber-50 text-amber-700',
    info: 'border-sky-200 bg-sky-50 text-sky-700',
  };

  pageAlert.className = `mb-4 rounded-xl border px-4 py-3 text-sm ${styles[type] ?? styles.info}`;
  pageAlert.textContent = message;
  pageAlert.classList.remove('hidden');

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearAlert() {
  pageAlert.classList.add('hidden');
  pageAlert.textContent = '';
}

function openReasonModal(actionType, targetId, title) {
  reasonActionType.value = actionType;
  reasonTargetId.value = targetId;
  reasonModalTitle.textContent = title;
  reasonInput.value = '';

  reasonModal.classList.remove('hidden');
  reasonModal.classList.add('flex');
}

function closeReasonModal() {
  reasonModal.classList.add('hidden');
  reasonModal.classList.remove('flex');
  reasonInput.value = '';
}

async function submitReasonModal() {
  const actionType = reasonActionType.value;
  const targetId = reasonTargetId.value;
  const reason = reasonInput.value.trim();

  if (!reason) {
    showAlert('Debes ingresar un motivo.', 'warning');
    return;
  }

  const originalText = btnConfirmReasonModal.textContent;
  btnConfirmReasonModal.textContent = 'Procesando...';
  btnConfirmReasonModal.disabled = true;

  try {
    let url = '';
    if (actionType === 'void_sale') {
      url = `/sales/${targetId}/void`;
    } else if (actionType === 'cancel_fel') {
      url = `/fel-documents/${targetId}/cancel`;
    } else {
      showAlert('Acción no válida.', 'error');
      return;
    }

    const res = await fetch(url, {
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
      showAlert(data?.message ?? 'Error al procesar la solicitud.', 'error');
      return;
    }

    closeReasonModal();

    if (actionType === 'void_sale') {
      showAlert('Venta anulada correctamente', 'success');
    } else {
      showAlert(data?.message ?? 'Documento FEL anulado correctamente', 'success');
    }

    loadSales();

  } catch (e) {
    showAlert('Error de conexión: ' + e.message, 'error');
  } finally {
    btnConfirmReasonModal.textContent = originalText;
    btnConfirmReasonModal.disabled = false;
  }
}

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

function voidSale(id) {
  openReasonModal('void_sale', id, 'Motivo de anulación de venta');
}

function cancelFel(felId) {
  openReasonModal('cancel_fel', felId, 'Motivo de anulación FEL');
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
    showAlert('No se encontró la venta para emitir FEL.', 'error');
    return;
  }

 if (receiverType !== 'CF' && taxid === '') {
    showAlert('Debes ingresar un NIT o CUI.', 'warning');
    return;
  }

  if ((receiverType === 'NIT' || receiverType === 'CUI') && receiverName === '') {
    showAlert('Debes ingresar el nombre del receptor FEL.', 'warning');
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
      showAlert(data?.message ?? 'Error al emitir FEL', 'error');
      return;
    }

    closeFelModal();
    showAlert(data?.message ?? `Factura FEL emitida correctamente para la venta #${saleId}`, 'success');
    window.open(`/sales/${saleId}/ticket`, '_blank');
    loadSales();
  } catch (e) {
    showAlert('Error de conexión: ' + e.message, 'error');
  } finally {
    btnConfirmFel.textContent = originalText;
    btnConfirmFel.disabled = false;
  }
}



btnLookupNit.addEventListener('click', async () => {
  const nit = felTaxid.value.trim();

  if (!nit) {
    showAlert('Ingresa un NIT.', 'warning');
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
      showAlert(data.message ?? 'Error al consultar NIT', 'error');
      return;
    }

    if (data.data?.name) {
      felReceiverName.value = data.data.name;
      showAlert('NIT consultado correctamente.', 'success');
    } else {
      showAlert('NIT válido pero sin nombre retornado.', 'warning');
    }
  } catch (e) {
    showAlert('Error de conexión: ' + e.message, 'error');
  } finally {
    btnLookupNit.textContent = originalText;
    btnLookupNit.disabled = false;
  }
});

btnConfirmFel.addEventListener('click', emitFelFromModal);
btnCloseFelModal.addEventListener('click', closeFelModal);
btnCancelFelModal.addEventListener('click', closeFelModal);
felReceiverType.addEventListener('change', syncFelUI);
btnCloseReasonModal.addEventListener('click', closeReasonModal);
btnCancelReasonModal.addEventListener('click', closeReasonModal);
btnConfirmReasonModal.addEventListener('click', submitReasonModal);

document.getElementById('btn_load').addEventListener('click', () => {
  clearAlert();
  loadSales();
});

syncFelUI();
loadSales();
</script>

@endsection