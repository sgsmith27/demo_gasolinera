@extends('layouts.app', ['title' => 'Nuevo gasto'])

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5">
    <h1 class="text-xl font-semibold mb-4">Registrar gasto</h1>

    <div class="grid gap-3">
        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Fecha</span>
            <input id="expense_date" type="date" value="{{ $today }}" class="w-full border rounded-lg px-3 py-2">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Categoría</span>
            <select id="category" class="w-full border rounded-lg px-3 py-2">
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Concepto</span>
            <input id="concept" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="Ej. Pago de electricidad">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Monto (Q)</span>
            <input id="amount_q" type="number" step="0.01" min="0.01" class="w-full border rounded-lg px-3 py-2" placeholder="0.00">
        </label>

        <label class="text-sm">
            <span class="block mb-1 text-gray-700">Notas</span>
            <textarea id="notes" class="w-full border rounded-lg px-3 py-2" rows="3"></textarea>
        </label>

        <button id="btn_submit" class="bg-black text-white rounded-lg px-4 py-2">
            Registrar gasto
        </button>

        <div id="result" class="text-sm mt-2"></div>
    </div>
</div>

<script>
const result = document.getElementById('result');

document.getElementById('btn_submit').addEventListener('click', async () => {
  result.textContent = 'Enviando...';

  const payload = {
    expense_date: document.getElementById('expense_date').value,
    category: document.getElementById('category').value,
    concept: document.getElementById('concept').value,
    amount_q: Number(document.getElementById('amount_q').value),
    notes: document.getElementById('notes').value || null,
  };

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('/expenses', {
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
      data = { message: 'Respuesta no JSON', raw: text.slice(0, 300) };
    }

    if (!res.ok) {
      result.innerHTML = `<span class="text-red-600">Error:</span> ${data.message ?? 'Error'}
        <pre class="whitespace-pre-wrap">${JSON.stringify(data, null, 2)}</pre>`;
      return;
    }

    result.innerHTML = `<span class="text-green-700 font-medium">OK:</span> Gasto #${data.data.id} registrado`;
    document.getElementById('concept').value = '';
    document.getElementById('amount_q').value = '';
    document.getElementById('notes').value = '';
  } catch (e) {
    result.innerHTML = `<span class="text-red-600">Error:</span> ${e.message}`;
  }
});
</script>
@endsection