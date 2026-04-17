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

@php
    $fuelCards = collect($nozzles)
        ->unique('fuel_id')
        ->map(function ($n) {
            return (object) [
                'fuel_id' => $n->fuel_id,
                'fuel_name' => $n->fuel_name,
            ];
        })
        ->sortBy('fuel_name')
        ->values();

    $pumpCards = collect($nozzles)
        ->unique('pump_id')
        ->map(function ($n) {
            return (object) [
                'pump_id' => $n->pump_id,
                'pump_code' => $n->pump_code,
            ];
        })
        ->sortBy('pump_code')
        ->values();
@endphp

@section('content')
<div id="page_alert" class="hidden mb-4 rounded-xl border px-4 py-3 text-sm"></div>

<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Nueva venta</h1>

    <style>
        .selection-card {
            border: 1px solid rgb(203 213 225);
            border-radius: 1rem;
            padding: 1rem;
            background: white;
            transition: all .15s ease;
            min-height: 92px;
        }

        .selection-card:hover {
            border-color: rgb(148 163 184);
            transform: translateY(-1px);
        }

        .selection-card.active-fuel {
            background: rgb(30 64 175);
            color: white;
            border-color: rgb(30 64 175);
            box-shadow: 0 10px 20px rgba(30, 64, 175, .18);
        }

        .selection-card.active-pump {
            background: rgb(22 163 74);
            color: white;
            border-color: rgb(22 163 74);
            box-shadow: 0 10px 20px rgba(22, 163, 74, .18);
        }

        .selection-card.disabled-card {
            opacity: .45;
            cursor: not-allowed;
        }

        .pill-button {
            border: 1px solid rgb(203 213 225);
            border-radius: .9rem;
            padding: .8rem 1rem;
            background: white;
            font-size: .95rem;
            font-weight: 600;
            transition: all .15s ease;
        }

        .pill-button.active {
            background: rgb(15 23 42);
            color: white;
            border-color: rgb(15 23 42);
        }

        .section-title {
            font-size: .92rem;
            font-weight: 700;
            color: rgb(51 65 85);
            margin-bottom: .65rem;
        }

        .kpi-box {
            border: 1px solid rgb(226 232 240);
            background: rgb(248 250 252);
            border-radius: .9rem;
            padding: .9rem 1rem;
        }

        .hidden-select-fallback {
            display: none;
        }

        @media (max-width: 640px) {
            .selection-card {
                min-height: 82px;
                padding: .9rem;
            }
        }
    </style>
@php
    $nozzlesForJs = collect($nozzles)->map(function ($n) {
        return [
            'id' => $n->id,
            'fuel_id' => $n->fuel_id,
            'fuel_name' => $n->fuel_name,
            'pump_id' => $n->pump_id,
            'pump_code' => $n->pump_code,
            'code' => $n->code,
        ];
    })->values();
@endphp
    <div class="grid gap-5">
        {{-- COMBUSTIBLE --}}
        <div>
            <div class="section-title">1. Selecciona combustible</div>
            <div id="fuel_cards" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($fuelCards as $fuel)
                    <button
                        type="button"
                        class="selection-card fuel-card text-center"
                        data-fuel-id="{{ $fuel->fuel_id }}"
                        data-fuel-name="{{ $fuel->fuel_name }}"
                    >
                        <div class="text-base font-semibold">{{ $fuel->fuel_name }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- BOMBA --}}
        <div>
            <div class="section-title">2. Selecciona bomba</div>
            <div id="pump_cards" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($pumpCards as $pump)
                    <button
                        type="button"
                        class="selection-card pump-card text-center"
                        data-pump-id="{{ $pump->pump_id }}"
                        data-pump-code="{{ $pump->pump_code }}"
                    >
                        <div class="text-xs uppercase tracking-wide opacity-80">Bomba</div>
                        <div class="text-2xl font-bold">{{ $pump->pump_code }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- SELECT OCULTO DE RESPALDO --}}
        <div class="hidden-select-fallback">
            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Manguera</span>
                <select id="nozzle_id" class="w-full border rounded-lg px-3 py-2">
                    @foreach($nozzles as $n)
                        <option
                            value="{{ $n->id }}"
                            data-fuel-id="{{ $n->fuel_id }}"
                            data-pump-id="{{ $n->pump_id }}"
                            data-fuel-name="{{ $n->fuel_name }}"
                            data-pump-code="{{ $n->pump_code }}"
                        >
                            {{ $n->pump_code }} / {{ $n->code }} — {{ $n->fuel_name }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        {{-- RESUMEN DE SELECCIÓN --}}
        <div class="grid gap-3 md:grid-cols-3">
            <div class="kpi-box">
                <div class="text-xs text-slate-500">Combustible seleccionado</div>
                <div id="selected_fuel_text" class="mt-1 font-semibold text-slate-800">—</div>
            </div>

            <div class="kpi-box">
                <div class="text-xs text-slate-500">Bomba seleccionada</div>
                <div id="selected_pump_text" class="mt-1 font-semibold text-slate-800">—</div>
            </div>

            <div id="price_box" class="kpi-box">
                <div class="text-xs text-slate-500">Precio vigente</div>
                <div class="mt-1 font-semibold text-slate-800">
                    <span id="price_gal">—</span> / galón
                </div>
            </div>
        </div>

        {{-- MODO --}}
        <div>
            <div class="section-title">3. Modo de venta</div>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="pill-button mode-btn active" data-mode="amount">
                    Por monto (Q)
                </button>
                <button type="button" class="pill-button mode-btn" data-mode="volume">
                    Por galones
                </button>
            </div>

            <select id="sale_mode" class="hidden">
                <option value="amount" selected>Por monto (Q)</option>
                <option value="volume">Por galones</option>
            </select>
        </div>

        {{-- DATOS DE VENTA --}}
        <div class="grid gap-3 md:grid-cols-2">
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

            <div id="total_box" class="hidden kpi-box">
                <div class="text-xs text-slate-500">Total estimado</div>
                <div class="mt-1 text-lg font-semibold text-slate-800" id="total_q">—</div>
            </div>
        </div>

        {{-- MÉTODO DE PAGO --}}
        <div>
            <div class="section-title">4. Método de pago</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button type="button" class="pill-button payment-btn active" data-payment="cash">Efectivo</button>
                <button type="button" class="pill-button payment-btn" data-payment="card">Tarjeta</button>
                <button type="button" class="pill-button payment-btn" data-payment="transfer">Transferencia</button>
                <button type="button" class="pill-button payment-btn" data-payment="credit">Crédito</button>
            </div>

            <select id="payment_method" class="hidden">
                <option value="cash" selected>Efectivo</option>
                <option value="card">Tarjeta</option>
                <option value="transfer">Transferencia</option>
                <option value="credit">Crédito</option>
            </select>
        </div>

        {{-- CLIENTE CRÉDITO --}}
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

        {{-- ACCIONES --}}
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
const pageAlert = document.getElementById('page_alert');

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

const fuelCardButtons = document.querySelectorAll('.fuel-card');
const pumpCardButtons = document.querySelectorAll('.pump-card');
const modeButtons = document.querySelectorAll('.mode-btn');
const paymentButtons = document.querySelectorAll('.payment-btn');

const selectedFuelText = document.getElementById('selected_fuel_text');
const selectedPumpText = document.getElementById('selected_pump_text');

const nozzles = @json($nozzlesForJs);

let currentPricePerGallon = null;
let selectedFuelId = null;
let selectedFuelName = null;
let selectedPumpId = null;
let selectedPumpCode = null;
let selectedNozzleId = null;

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
    const fuelId = selected?.getAttribute('data-fuel-id');

    if (!fuelId) {
        priceGal.textContent = '—';
        currentPricePerGallon = null;
        recalcTotal();
        return;
    }

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

function updateSelectionSummary() {
    selectedFuelText.textContent = selectedFuelName ?? '—';
    selectedPumpText.textContent = selectedPumpCode ?? '—';
}

function setActiveButton(buttons, activeButton, activeClass) {
    buttons.forEach(btn => btn.classList.remove(activeClass));
    if (activeButton) {
        activeButton.classList.add(activeClass);
    }
}

function resolveNozzle(showError = true) {
    if (!selectedFuelId || !selectedPumpId) {
        selectedNozzleId = null;
        return;
    }

    const match = nozzles.find(n =>
        Number(n.fuel_id) === Number(selectedFuelId) &&
        Number(n.pump_id) === Number(selectedPumpId)
    );

    if (!match) {
        selectedNozzleId = null;
        nozzleSelect.value = '';
        priceGal.textContent = 'No definido';
        currentPricePerGallon = null;
        recalcTotal();

        if (showError) {
            showAlert('No existe una manguera configurada para esta combinación de combustible y bomba.', 'error');
        }
        return;
    }

    selectedNozzleId = Number(match.id);
    nozzleSelect.value = String(match.id);
    loadPrice();
}

function validateSelectionBeforeSubmit() {
    if (!selectedFuelId) {
        showAlert('Debes seleccionar un combustible.', 'warning');
        return false;
    }

    if (!selectedPumpId) {
        showAlert('Debes seleccionar una bomba.', 'warning');
        return false;
    }

    if (!selectedNozzleId) {
        showAlert('No se encontró una manguera válida para la combinación seleccionada.', 'error');
        return false;
    }

    return true;
}

function buildPayload(extra = {}) {
    const payload = {
        nozzle_id: selectedNozzleId ?? Number(nozzleSelect.value),
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
    clearAlert();
    result.textContent = 'Enviando...';

    if (!validateSelectionBeforeSubmit()) {
        result.textContent = '';
        return;
    }

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
            showAlert(`${data.message ?? 'Error'} (ver consola)`, 'error');
            console.error('Error detalle:', data);
            result.textContent = '';
            return;
        }

        const gallons = Number(data.data.gallons);

        if (extra.fel_emit === 1) {
            if (data.fel && data.fel.success) {
                showAlert(
                    `Venta #${data.data.id} registrada y FEL certificada (UUID: ${data.fel.fel_uuid})`,
                    'success'
                );

                window.open(`/sales/${data.data.id}/ticket`, '_blank');
            } else {
                showAlert(
                    `Venta registrada correctamente, pero la FEL falló: ${data.fel?.message ?? 'Error desconocido'}`,
                    'warning'
                );

                console.error('Error FEL:', data.fel);
            }
        } else {
            showAlert(
                `Venta registrada correctamente por Q${Number(data.data.total_amount_q).toFixed(2)} (${gallons.toFixed(3)} gal)`,
                'success'
            );
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

        result.textContent = '';
    } catch (e) {
        showAlert(`Error de conexión: ${e.message}`, 'error');
        console.error('Error JS:', e);
        result.textContent = '';
    } finally {
        btnSubmit.disabled = false;
        btnSaveAndFel.disabled = false;
    }
}

fuelCardButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        selectedFuelId = Number(btn.dataset.fuelId);
        selectedFuelName = btn.dataset.fuelName;

        setActiveButton(fuelCardButtons, btn, 'active-fuel');
        updateSelectionSummary();
        resolveNozzle(false);
    });
});

pumpCardButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        selectedPumpId = Number(btn.dataset.pumpId);
        selectedPumpCode = btn.dataset.pumpCode;

        setActiveButton(pumpCardButtons, btn, 'active-pump');
        updateSelectionSummary();
        resolveNozzle(false);
    });
});

modeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        mode.value = btn.dataset.mode;
        modeButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        toggleModeUI();
    });
});

paymentButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        paymentMethod.value = btn.dataset.payment;
        paymentButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        toggleCustomerField();
    });
});

btnSubmit.addEventListener('click', async () => {
    await submitSale();
});

btnSaveAndFel.addEventListener('click', () => {
    if (!validateSelectionBeforeSubmit()) {
        return;
    }
    openFelModal();
});

btnCloseFelModal.addEventListener('click', closeFelModal);
btnCancelFelModal.addEventListener('click', closeFelModal);

btnConfirmFel.addEventListener('click', async () => {
    const receiverType = felReceiverType.value;
    const taxid = felTaxid.value.trim();
    const receiverName = felReceiverName.value.trim();

    if (receiverType !== 'CF' && taxid === '') {
        showAlert('Debes ingresar un NIT o CUI.', 'warning');
        return;
    }

    if ((receiverType === 'NIT' || receiverType === 'CUI') && receiverName === '') {
        showAlert('Debes ingresar el nombre del receptor.', 'warning');
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
        showAlert('Ingresa un NIT.', 'warning');
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

paymentMethod.addEventListener('change', toggleCustomerField);
mode.addEventListener('change', toggleModeUI);
nozzleSelect.addEventListener('change', loadPrice);
gallonsInput.addEventListener('input', recalcTotal);
felReceiverType.addEventListener('change', syncFelUI);

toggleCustomerField();
toggleModeUI();
syncFelUI();
updateSelectionSummary();
</script>
@endsection