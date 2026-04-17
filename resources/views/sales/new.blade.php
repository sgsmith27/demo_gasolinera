@extends('layouts.app', ['title' => 'Nueva venta'])

@if(isset($shiftInfo) && $shiftInfo)
    <div class="mb-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm">
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

    $fuelColorMap = [
        'Gasolina Regular' => 'regular',
        'Gasolina Super' => 'super',
        'Diesel' => 'diesel',
    ];

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

@section('content')
<div id="page_alert" class="hidden mb-4 rounded-2xl border px-4 py-3 text-sm shadow-sm"></div>

<div class="rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 md:p-6 shadow-sm">
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Nueva venta</h1>
            <p class="text-sm text-slate-500">Interfaz rápida tipo POS para registro de combustible</p>
        </div>

    </div>

    <style>
        .pos-section-title {
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: .02em;
            color: rgb(51 65 85);
            margin-bottom: .8rem;
            text-transform: uppercase;
        }

        .pos-card {
            border-radius: 1.4rem;
            border: 1px solid rgb(226 232 240);
            background: white;
            min-height: 118px;
            padding: 1rem;
            transition: all .18s ease;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        }

        .pos-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, .08);
        }

        .fuel-card {
            color: white;
            position: relative;
            overflow: hidden;
        }

        .fuel-card::after {
            content: '';
            position: absolute;
            inset: auto -30px -30px auto;
            width: 85px;
            height: 85px;
            border-radius: 9999px;
            background: rgba(255,255,255,.12);
        }

        .fuel-card .fuel-icon {
            font-size: 2.25rem;
            line-height: 1;
            margin-bottom: .65rem;
            opacity: .95;
        }

        .fuel-regular {
            background: linear-gradient(135deg, #f59e0b, #fb923c);
            border-color: #f59e0b;
        }

        .fuel-super {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-color: #16a34a;
        }

        .fuel-diesel {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border-color: #1d4ed8;
        }

        .fuel-default {
            background: linear-gradient(135deg, #334155, #475569);
            border-color: #334155;
        }

        .fuel-card.active-fuel,
        .pump-card.active-pump,
        .payment-btn.active,
        .mode-btn.active {
            outline: 3px solid rgba(15, 23, 42, .18);
            outline-offset: 2px;
            transform: scale(0.99);
        }

        .pump-card {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .pump-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 9999px;
            background: rgb(15 23 42);
            color: white;
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0 auto .7rem auto;
            box-shadow: 0 10px 18px rgba(15, 23, 42, .15);
        }

        .pill-button {
            border: 1px solid rgb(203 213 225);
            border-radius: 1rem;
            padding: .95rem 1rem;
            background: white;
            font-size: .95rem;
            font-weight: 700;
            transition: all .15s ease;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .03);
        }

        .pill-button:hover {
            border-color: rgb(148 163 184);
        }

        .pill-button.active {
            background: rgb(15 23 42);
            color: white;
            border-color: rgb(15 23 42);
        }

        .sale-kpi {
            border-radius: 1.25rem;
            border: 1px solid rgb(226 232 240);
            background: white;
            padding: 1rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        }

        .sale-panel {
            border-radius: 1.5rem;
            border: 1px solid rgb(226 232 240);
            background: rgba(255,255,255,.82);
            backdrop-filter: blur(6px);
            padding: 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        }

        .pos-input {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgb(203 213 225);
            background: white;
            padding: .85rem 1rem;
            font-size: .98rem;
            transition: all .15s ease;
        }

        .pos-input:focus {
            outline: none;
            border-color: rgb(59 130 246);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
        }

        .action-primary,
        .action-secondary {
            border-radius: 1rem;
            padding: .95rem 1.2rem;
            font-weight: 800;
            transition: all .18s ease;
            box-shadow: 0 10px 20px rgba(15, 23, 42, .08);
        }

        .action-primary {
            background: rgb(15 23 42);
            color: white;
        }

        .action-primary:hover {
            background: rgb(30 41 59);
        }

        .action-secondary {
            background: rgb(79 70 229);
            color: white;
        }

        .action-secondary:hover {
            background: rgb(67 56 202);
        }

        .hidden-select-fallback {
            display: none;
        }

        @media (max-width: 640px) {
            .pos-card {
                min-height: 104px;
                padding: .9rem;
            }

            .fuel-card .fuel-icon {
                font-size: 2rem;
            }

            .pump-badge {
                width: 48px;
                height: 48px;
                font-size: 1.25rem;
            }
        }
    </style>

    <div class="grid gap-5 xl:grid-cols-[1.5fr,1fr]">
        <div class="space-y-5">
            <div class="sale-panel">
                <div class="pos-section-title">1. Selecciona combustible</div>
                <div id="fuel_cards" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($fuelCards as $fuel)
                        @php
                            $fuelStyle = $fuelColorMap[$fuel->fuel_name] ?? 'default';
                        @endphp
                        <button
                            type="button"
                            class="pos-card fuel-card fuel-{{ $fuelStyle }} text-center"
                            data-fuel-id="{{ $fuel->fuel_id }}"
                            data-fuel-name="{{ $fuel->fuel_name }}"
                        >
                            <div class="fuel-icon">⛽</div>
                            <div class="text-base font-bold leading-tight">{{ $fuel->fuel_name }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="sale-panel">
                <div class="pos-section-title">2. Selecciona bomba</div>
                <div id="pump_cards" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($pumpCards as $pump)
                        <button
                            type="button"
                            class="pos-card pump-card text-center"
                            data-pump-id="{{ $pump->pump_id }}"
                            data-pump-code="{{ $pump->pump_code }}"
                        >
                            <div class="pump-badge">{{ $pump->pump_code }}</div>
                            <div class="text-sm font-bold text-slate-800">Bomba {{ $pump->pump_code }}</div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="sale-panel">
                <div class="pos-section-title">3. Modo de venta</div>
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

            <div class="sale-panel">
                <div class="pos-section-title">4. Método de pago</div>
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
        </div>

        <div class="space-y-5">
            <div class="sale-panel">
                <div class="pos-section-title">Ingreso de datos</div>

                <div class="grid gap-4">
                    <label class="text-sm" id="amount_wrap">
                        <span class="mb-1.5 block font-medium text-slate-700">Monto (Q)</span>
                        <input id="amount_q" type="number" step="0.01" min="0.01"
                               class="pos-input" placeholder="100.00">
                    </label>

                    <label class="text-sm hidden" id="liters_wrap">
                        <span class="mb-1.5 block font-medium text-slate-700">Galones</span>
                        <input id="liters" type="number" step="0.001" min="0.001"
                               class="pos-input" placeholder="2.000">
                    </label>

                    <label class="text-sm hidden" id="customer_wrap">
                        <span class="mb-1.5 block font-medium text-slate-700">Cliente crédito</span>
                        <select id="customer_id" class="pos-input">
                            <option value="">Selecciona un cliente</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }} @if($customer->nit) — {{ $customer->nit }} @endif
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div id="total_box" class="sale-kpi hidden">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Total estimado</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900" id="total_q">—</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sale-kpi">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Combustible seleccionado</div>
                    <div id="selected_fuel_text" class="mt-2 text-lg font-bold text-slate-900">—</div>
                </div>

                <div class="sale-kpi">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Bomba seleccionada</div>
                    <div id="selected_pump_text" class="mt-2 text-lg font-bold text-slate-900">—</div>
                </div>

                <div class="sale-kpi sm:col-span-2">
                    <div class="text-xs uppercase tracking-wide text-slate-500">Precio vigente</div>
                    <div class="mt-2 text-lg font-bold text-slate-900">
                        <span id="price_gal">—</span> / galón
                    </div>
                </div>
            </div>

            <div class="sale-panel">
                <div class="pos-section-title">Acciones</div>
                <div class="flex flex-wrap gap-3">
                    <button id="btn_submit" type="button" class="action-primary">
                        Registrar venta
                    </button>

                    <button type="button" id="btn-save-and-fel" class="action-secondary">
                        Registrar y emitir FEL
                    </button>
                </div>

                <div id="result" class="mt-3 text-sm"></div>
            </div>
        </div>
    </div>

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
</div>

<div id="fel_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-800">Datos para emitir FEL</h2>
            <button type="button" id="btn_close_fel_modal" class="text-slate-500 hover:text-slate-700">✕</button>
        </div>

        <div class="space-y-4 px-4 py-4">
            <label class="text-sm">
                <span class="block mb-1 text-gray-700">Tipo de receptor</span>
                <select id="fel_receiver_type" class="pos-input">
                    <option value="CF">Consumidor Final</option>
                    <option value="NIT">NIT</option>
                    <option value="CUI">CUI</option>
                </select>
            </label>

            <div id="fel_taxid_wrap" class="hidden">
                <label class="text-sm">
                    <span class="block mb-1 text-gray-700">NIT / CUI</span>
                    <div class="flex gap-2">
                        <input id="fel_taxid" type="text" class="pos-input"
                               placeholder="Ingrese NIT o CUI">
                        <button type="button" id="btn_lookup_nit"
                                class="hidden rounded-xl bg-blue-600 px-4 py-2 text-white whitespace-nowrap">
                            Buscar
                        </button>
                    </div>
                </label>
            </div>

            <div id="fel_name_wrap" class="hidden">
                <label class="text-sm">
                    <span class="block mb-1 text-gray-700">Nombre receptor</span>
                    <input id="fel_receiver_name" type="text" class="pos-input"
                           placeholder="Nombre del receptor">
                </label>
            </div>

            <div class="rounded-xl border bg-slate-50 p-3 text-sm text-slate-600">
                La venta se guardará primero y luego se intentará emitir FEL.
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t px-4 py-3">
            <button type="button" id="btn_cancel_fel_modal" class="rounded-xl border px-4 py-2 text-slate-700">
                Cancelar
            </button>
            <button type="button" id="btn_confirm_fel" class="rounded-xl bg-indigo-600 px-4 py-2 text-white">
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

    pageAlert.className = `mb-4 rounded-2xl border px-4 py-3 text-sm shadow-sm ${styles[type] ?? styles.info}`;
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