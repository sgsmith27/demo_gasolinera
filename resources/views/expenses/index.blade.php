@extends('layouts.app', ['title' => 'Gastos operativos'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Gastos operativos</h1>
        <a href="/expenses/new" class="border rounded-lg px-4 py-2">Nuevo gasto</a>
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

function money(v) {
  return `Q${Number(v).toFixed(2)}`;
}

async function loadExpenses() {
  out.textContent = 'Cargando...';

  const from = document.getElementById('from').value;
  const to = document.getElementById('to').value;
  document.getElementById('btn_pdf').href = `/expenses/pdf?from=${from}&to=${to}`;

  const res = await fetch(`/expenses-data?from=${from}&to=${to}`, {
    headers: { 'Accept': 'application/json' }
  });

  const text = await res.text();
  let data;
  try {
    data = JSON.parse(text);
  } catch {
    out.innerHTML = `<span class="text-red-600">Error:</span> respuesta no JSON`;
    return;
  }

  if (!res.ok) {
    out.innerHTML = `<span class="text-red-600">Error:</span> ${JSON.stringify(data)}`;
    return;
  }

  let html = `
    <div class="mb-4">
      <div class="font-medium">Total general</div>
      <div>${money(data.grand_total.total_q)}</div>
    </div>
  `;

  html += `
    <div class="mb-5">
      <div class="font-medium mb-2">Totales por categoría</div>
      <div class="overflow-auto border rounded-lg">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2">Categoría</th>
              <th class="px-3 py-2">Registros</th>
              <th class="px-3 py-2">Total Q</th>
            </tr>
          </thead>
          <tbody>
            ${data.totals_by_category.map(r => `
              <tr class="border-t">
                <td class="px-3 py-2">${r.category}</td>
                <td class="px-3 py-2">${r.items_count}</td>
                <td class="px-3 py-2">${money(r.total_q)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>
  `;

  html += `
    <div>
      <div class="font-medium mb-2">Detalle</div>
      <div class="overflow-auto border rounded-lg">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2">Fecha</th>
              <th class="px-3 py-2">Categoría</th>
              <th class="px-3 py-2">Concepto</th>
              <th class="px-3 py-2">Monto</th>
              <th class="px-3 py-2">Notas</th>
              <th class="px-3 py-2">Estado</th>
              <th class="px-3 py-2">Acciones</th>
            </tr>
          </thead>
          <tbody>
            ${data.expenses.map(r => `
              <tr class="border-t">
                <td class="px-3 py-2">${r.expense_date}</td>
                <td class="px-3 py-2">${r.category}</td>
                <td class="px-3 py-2">${r.concept}</td>
                <td class="px-3 py-2">${money(r.amount_q)}</td>
                <td class="px-3 py-2">${r.notes ?? ''}</td>
                <td class="px-3 py-2">${r.status}</td>
                <td class="px-3 py-2">
                  ${r.status === 'active'
                    ? `<button class="text-red-600 underline" onclick="voidExpense(${r.id})">Anular</button>`
                    : `<span class="text-gray-400">Anulado</span>`}
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </div>
  `;

  out.innerHTML = html;
}

async function voidExpense(id) {
  const reason = prompt('Motivo de anulación:');
  if (!reason) return;

  const res = await fetch(`/expenses/${id}/void`, {
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
    data = { message: 'Respuesta no JSON', raw: text.slice(0, 300) };
  }

  if (!res.ok) {
    alert(data.message ?? 'Error al anular gasto');
    return;
  }

  alert('Gasto anulado correctamente');
  loadExpenses();
}

document.getElementById('btn_load').addEventListener('click', loadExpenses);
loadExpenses();
</script>

@endsection