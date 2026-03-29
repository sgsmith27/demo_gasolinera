@extends('layouts.app', ['title' => 'Nueva venta'])

@if(isset($shiftInfo) && $shiftInfo)
    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
        @if($shiftInfo->assignment_mode === 'fixed')
            Turno asignado a bomba fija:
            <span class="font-semibold">{{ $shiftInfo->pump?->code ?? '—' }}</span>
        @else
            Turno en modo libre: puedes vender desde cualquier bomba activa.
        @endif
    </div>
@endif

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Nueva venta</h1>

    <div class="grid gap-3">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Manguera</span>
            <select id="nozzle_id" class="w-full border rounded-lg px-3 py-2">
                @foreach($nozzles as $n)
                    <option value="{{ $n->id }}" data-fuel-id="{{ $n->fuel_id }}">
                        {{ $n->pump_code }} / {{ $n->code }} — {{ $n->fuel_name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Modo</span>
            <select id="sale_mode" class="w-full border rounded-lg px-3 py-2">
                <option value="amount">Por monto (Q)</option>
                <option value="volume">Por galones</option>
            </select>
        </label>

        <label class="text-sm" id="amount_wrap">
            <span class="block mb-1 text-gray-700">Monto (Q)</span>
            <input id="amount_q" type="number" step="0.01" min="0.01"
                   class="w-full border rounded-lg px-3 py-2" placeholder="100.00">
        </label>

        <label class="text-sm hidden" id="liters_wrap">
            <span class="block mb-1 text-gray-700">Galones</span>
            <input id="liters" type="number" step="0.001" min="0.001"
                   class="w-full border rounded-lg px-3 py-2" placeholder="2.000">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Método de pago</span>
            <select id="payment_method" class="w-full border rounded-lg px-3 py-2">
                <option value="cash">Efectivo</option>
                <option value="card">Tarjeta</option>
                <option value="transfer">Transferencia</option>
                <option value="credit">Crédito</option>
            </select>
        </label>

        <label class="text-sm hidden" id="customer_wrap">
            <span class="block mb-1 text-gray-700">Cliente crédito</span>
            <select id="customer_id" class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecciona un cliente</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">
                        {{ $customer->name }} @if($customer->nit) — {{ $customer->nit }} @endif
                    </option>
                @endforeach
            </select>
        </label>

        <div id="price_box" class="text-sm text-gray-700 bg-gray-50 border rounded-lg p-3">
            Precio: <span id="price_gal">—</span> / galón
        </div>

        <div id="total_box" class="hidden text-sm text-gray-700 bg-gray-50 border rounded-lg p-3">
            Total estimado: <span class="font-semibold" id="total_q">—</span>
        </div>

        <div class="flex flex-wrap gap-2">
            <button id="btn_submit" type="button" class="bg-black text-white rounded-lg px-4 py-2">
                Registrar venta
            </button>

            <button type="button" id="btn-save-and-fel" class="bg-indigo-600 text-white rounded-lg px-4 py-2">
                Registrar y emitir FEL
            </button>
        </div>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<div id="fel_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Datos para emitir FEL</h2>
            <button type="button" id="btn_close_fel_modal" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="space-y-4 px-4 py-4">
            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Tipo de receptor</span>
                <select id="fel_receiver_type" class="w-full border rounded-lg px-3 py-2">
                    <option value="CF">Consumidor Final</option>
                    <option value="NIT">NIT</option>
                    <option value="CUI">CUI</option>
                </select>
            </label>

            <div id="fel_taxid_wrap" class="hidden">
                <label class="text-sm">
                    <span class="block mb-1 text-gray-700">NIT / CUI</span>
                    <div class="flex gap-2">
                        <input id="fel_taxid" type="text" class="w-full border rounded-lg px-3 py-2"
                               placeholder="Ingrese NIT o CUI">
                        <button type="button" id="btn_lookup_nit"
                                class="hidden bg-blue-600 text-white rounded-lg px-3 py-2 whitespace-nowrap">
                            Buscar
                        </button>
                    </div>
                </label>
            </div>

            <div id="fel_name_wrap" class="hidden">
                <label class="text-sm">
                    <span class="block mb-1 text-gray-700">Nombre receptor</span>
                    <input id="fel_receiver_name" type="text" class="w-full border rounded-lg px-3 py-2"
                           placeholder="Nombre del receptor">
                </label>
            </div>

            <div class="rounded-lg border bg-slate-50 p-3 text-sm text-slate-600">
                La venta se guardará primero y luego se intentará emitir FEL.
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t px-4 py-3">
            <button type="button" id="btn_cancel_fel_modal" class="rounded-lg border px-4 py-2 text-slate-700">
                Cancelar
            </button>
            <button type="button" id="btn_confirm_fel" class="rounded-lg bg-indigo-600 px-4 py-2 text-white">
                Guardar y emitir FEL
            </button>
        </div>
    </div>
</div>

<script>
const mode = document.getElementById('sale_mode');
const amountWrap = document.getElementById('amount_wrap');
const gallonsWrap = document.getElementById('liters_wrap');
const result = document.getElementById('result');

const nozzleSelect = document.getElementById('nozzle_id');
const priceGal = document.getElementById('price_gal');

const gallonsInput = document.getElementById('liters');
const amountInput = document.getElementById('amount_q');
const totalBox = document.getElementById('total_box');
const totalQEl = document.getElementById('total_q');

const paymentMethod = document.getElementById('payment_method');
const customerWrap = document.getElementById('customer_wrap');
const customerId = document.getElementById('customer_id');

const btnSubmit = document.getElementById('btn_submit');
const btnSaveAndFel = document.getElementById('btn-save-and-fel');

const felModal = document.getElementById('fel_modal');
const btnCloseFelModal = document.getElementById('btn_close_fel_modal');
const btnCancelFelModal = document.getElementById('btn_cancel_fel_modal');
const btnConfirmFel = document.getElementById('btn_confirm_fel');

const felReceiverType = document.getElementById('fel_receiver_type');
const felTaxidWrap = document.getElementById('fel_taxid_wrap');
const felTaxid = document.getElementById('fel_taxid');
const felNameWrap = document.getElementById('fel_name_wrap');
const felReceiverName = document.getElementById('fel_receiver_name');
const btnLookupNit = document.getElementById('btn_lookup_nit');

let currentPricePerGallon = null;

function toggleModeUI() {
    if (mode.value === 'amount') {
        amountWrap.classList.remove('hidden');
        gallonsWrap.classList.add('hidden');
        totalBox.classList.add('hidden');
    } else {
        gallonsWrap.classList.remove('hidden');
        amountWrap.classList.add('hidden');
        recalcTotal();
    }
}

function toggleCustomerField() {
    if (paymentMethod.value === 'credit') {
        customerWrap.classList.remove('hidden');
    } else {
        customerWrap.classList.add('hidden');
        customerId.value = '';
    }
}

function recalcTotal() {
    if (mode.value !== 'volume') {
        totalBox.classList.add('hidden');
        totalQEl.textContent = '—';
        return;
    }

    totalBox.classList.remove('hidden');

    const gallons = Number(gallonsInput.value);
    if (!currentPricePerGallon || !gallons || gallons <= 0) {
        totalQEl.textContent = '—';
        return;
    }

    const total = gallons * currentPricePerGallon;
    totalQEl.textContent = `Q${total.toFixed(2)}`;
}

async function loadPrice() {
    const selected = nozzleSelect.options[nozzleSelect.selectedIndex];
    const fuelId = selected.getAttribute('data-fuel-id');

    priceGal.textContent = 'Cargando...';
    currentPricePerGallon = null;

    try {
        const res = await fetch(`/fuels/${fuelId}/current-price`, {
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
            priceGal.textContent = 'No definido';
            currentPricePerGallon = null;
            recalcTotal();
            return;
        }

        currentPricePerGallon = Number(data.price_per_gallon);
        priceGal.textContent = `Q${currentPricePerGallon.toFixed(2)}`;
        recalcTotal();
    } catch (e) {
        priceGal.textContent = 'Error';
        currentPricePerGallon = null;
        recalcTotal();
    }
}

function openFelModal() {
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
        felTaxidWrap.classList.add('hidden');
        felNameWrap.classList.add('hidden');
        btnLookupNit.classList.add('hidden');
        felTaxid.value = '';
        felReceiverName.value = '';
        return;
    }

    felTaxidWrap.classList.remove('hidden');
    felNameWrap.classList.remove('hidden');

    if (type === 'NIT') {
        btnLookupNit.classList.remove('hidden');
    } else {
        btnLookupNit.classList.add('hidden');
    }
}

function buildPayload(extra = {}) {
    const payload = {
        nozzle_id: Number(nozzleSelect.value),
        sale_mode: mode.value,
        payment_method: paymentMethod.value,
        ...extra
    };

    if (mode.value === 'amount') {
        payload.amount_q = Number(amountInput.value);
    } else {
        payload.gallons = Number(gallonsInput.value);
    }

    if (paymentMethod.value === 'credit') {
        payload.customer_id = Number(customerId.value);
    }

    return payload;
}

async function submitSale(extra = {}) {
    result.textContent = 'Enviando...';
    btnSubmit.disabled = true;
    btnSaveAndFel.disabled = true;
    const payload = buildPayload(extra);

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          
        const res = await fetch('/sales', {
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
            data = {
                message: 'Respuesta no-JSON (probable error HTML)',
                raw: text.slice(0, 300)
            };
        }

        if (!res.ok) {
            result.innerHTML = `<span class="text-red-600">Error:</span> ${data.message ?? 'Error'}
                <pre class="whitespace-pre-wrap">${JSON.stringify(data, null, 2)}</pre>`;
            return;
        }

        const gallons = Number(data.data.gallons);

        if (extra.fel_emit === 1) {
          if (data.fel && data.fel.success) {
              result.innerHTML = `
                  <div class="text-green-700 font-medium">
                      ✔ Venta #${data.data.id} registrada y FEL certificada
                  </div>
                  <div class="text-xs text-gray-600">
                      UUID: ${data.fel.fel_uuid}
                  </div>
              `;

              // 🔥 abrir ticket automáticamente
              window.open(`/sales/${data.data.id}/ticket`, '_blank');

          } else {
              result.innerHTML = `
                  <div class="text-yellow-700 font-medium">
                      ⚠ Venta registrada pero FEL falló
                  </div>
                  <div class="text-xs text-red-600">
                      ${data.fel?.message ?? 'Error desconocido'}
                  </div>
              `;
          }
      } else {
          result.innerHTML = `
              <span class="text-green-700 font-medium">OK:</span>
              Venta #${data.data.id} — Q${Number(data.data.total_amount_q).toFixed(2)} — ${gallons.toFixed(3)} gal
          `;
      }

        if (mode.value === 'amount') {
            amountInput.value = '';
        } else {
            gallonsInput.value = '';
            recalcTotal();
        }

        if (paymentMethod.value === 'credit') {
            customerId.value = '';
        }

        felTaxid.value = '';
        felReceiverName.value = '';
        felReceiverType.value = 'CF';
        syncFelUI();

    } catch (e) {
        result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
    } finally {
    btnSubmit.disabled = false;
    btnSaveAndFel.disabled = false;
    }
}

btnSubmit.addEventListener('click', async () => {
    await submitSale();
});

btnSaveAndFel.addEventListener('click', () => {
    openFelModal();
});

btnCloseFelModal.addEventListener('click', closeFelModal);
btnCancelFelModal.addEventListener('click', closeFelModal);

btnConfirmFel.addEventListener('click', async () => {
    const receiverType = felReceiverType.value;
    const taxid = felTaxid.value.trim();
    const receiverName = felReceiverName.value.trim();

    if (receiverType !== 'CF' && taxid === '') {
        alert('Debes ingresar un NIT o CUI.');
        return;
    }

    if ((receiverType === 'NIT' || receiverType === 'CUI') && receiverName === '') {
        alert('Debes ingresar el nombre del receptor.');
        return;
    }

    closeFelModal();

    await submitSale({
        fel_emit: 1,
        fel_receiver_type: receiverType,
        fel_taxid: taxid,
        fel_receiver_name: receiverName
    });
});

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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

paymentMethod.addEventListener('change', toggleCustomerField);
mode.addEventListener('change', toggleModeUI);
nozzleSelect.addEventListener('change', loadPrice);
gallonsInput.addEventListener('input', recalcTotal);
felReceiverType.addEventListener('change', syncFelUI);

toggleCustomerField();
toggleModeUI();
loadPrice();
syncFelUI();
</script>
@endsection