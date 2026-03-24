@extends('layouts.app', ['title' => 'Detalle cuenta por cobrar'])

@section('content')
<div class="grid gap-4 md:gap-6">
    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Cuenta por cobrar #{{ $accountReceivable->id }}</h1>
        <p class="text-sm text-slate-500">Cliente: {{ $accountReceivable->customer?->name ?? '—' }}</p>
    </div>

    <a href="/accounts-receivable/{{ $accountReceivable->id }}/pdf"
       target="_blank"
       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
        PDF
    </a>
</div>

        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Monto original</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$accountReceivable->original_amount_q, 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Pagado</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$accountReceivable->paid_amount_q, 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Saldo pendiente</div>
            <div class="text-2xl font-bold mt-2">Q{{ number_format((float)$accountReceivable->balance_q, 2) }}</div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="text-sm text-slate-500">Estado</div>
            <div class="text-2xl font-bold mt-2">{{ strtoupper($accountReceivable->status) }}</div>
        </div>
    </div>

    @if($accountReceivable->status !== 'paid')
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Registrar abono</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/accounts-receivable/{{ $accountReceivable->id }}/payments" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Fecha y hora</span>
                    <input type="datetime-local" name="paid_at" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                </label>

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Monto</span>
                    <input type="number" step="0.01" min="0.01" name="amount_q" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                </label>

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Método</span>
                    <select name="payment_method" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta</option>
                        <option value="transfer">Transferencia</option>
                    </select>
                </label>

                <label class="text-sm">
                    <span class="block mb-1 text-slate-600">Notas</span>
                    <input type="text" name="notes" class="w-full border border-slate-300 rounded-xl px-3 py-2">
                </label>

                <div class="md:col-span-4">
                    <button type="submit" class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                        Registrar abono
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Historial de abonos</h2>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-[800px] w-full text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-slate-600">Fecha</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Monto</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Método</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Notas</th>
                        <th class="px-4 py-3 font-semibold text-slate-600">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accountReceivable->payments as $payment)
                        <tr>
                            <td class="px-4 py-3">{{ $payment->paid_at }}</td>
                            <td class="px-4 py-3">Q{{ number_format((float)$payment->amount_q, 2) }}</td>
                            <td class="px-4 py-3">{{ $payment->payment_method }}</td>
                            <td class="px-4 py-3">{{ $payment->notes ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $payment->creator?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-slate-500">No hay abonos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection