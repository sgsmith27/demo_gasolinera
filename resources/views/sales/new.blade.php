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

        <button id="btn_submit" class="bg-black text-white rounded-lg px-4 py-2">
            Registrar venta
        </button>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<script>
const mode = document.getElementById('sale_mode');
const amountWrap = document.getElementById('amount_wrap');
const gallonsWrap = document.getElementById('liters_wrap'); // el id viejo se puede dejar, pero representa galones
const result = document.getElementById('result');

const nozzleSelect = document.getElementById('nozzle_id');
const priceGal = document.getElementById('price_gal');

const gallonsInput = document.getElementById('liters'); // este input ahora representa galones
const totalBox = document.getElementById('total_box');
const totalQEl = document.getElementById('total_q');

let currentPricePerGallon = null;

const paymentMethod = document.getElementById('payment_method');
const customerWrap = document.getElementById('customer_wrap');
const customerId = document.getElementById('customer_id');

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

paymentMethod.addEventListener('change', toggleCustomerField);
toggleCustomerField();

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

nozzleSelect.addEventListener('change', loadPrice);
mode.addEventListener('change', toggleModeUI);
gallonsInput.addEventListener('input', recalcTotal);

toggleModeUI();
loadPrice();

document.getElementById('btn_submit').addEventListener('click', async () => {
  result.textContent = 'Enviando...';

  const payload = {
    nozzle_id: Number(nozzleSelect.value),
    sale_mode: mode.value,
    payment_method: document.getElementById('payment_method').value
  };

  if (mode.value === 'amount') {
    payload.amount_q = Number(document.getElementById('amount_q').value);
  } else {
    payload.gallons = Number(gallonsInput.value);
  }

  if (paymentMethod.value === 'credit') {
    payload.customer_id = Number(customerId.value);
  }

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

    result.innerHTML = `<span class="text-green-700 font-medium">OK:</span>
      Venta #${data.data.id} — Q${Number(data.data.total_amount_q).toFixed(2)} — ${gallons.toFixed(3)} gal`;

    // limpiar campo ingresado
    if (mode.value === 'amount') {
      document.getElementById('amount_q').value = '';
    } else {
      gallonsInput.value = '';
      recalcTotal();
    }

  } catch (e) {
    result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
});
</script>
@endsection