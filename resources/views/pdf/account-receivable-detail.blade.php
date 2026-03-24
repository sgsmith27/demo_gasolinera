<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1, h2 { margin-bottom: 10px; }
        .mb { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #f3f3f3; }
        .grid td { border: none; padding: 3px 0; }
    </style>
</head>
<body>
    <h1>Detalle de cuenta por cobrar #{{ $accountReceivable->id }}</h1>

    <div class="mb">
        <table class="grid">
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $accountReceivable->customer?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>NIT:</strong></td>
                <td>{{ $accountReceivable->customer?->nit ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Venta relacionada:</strong></td>
                <td>#{{ $accountReceivable->sale_id }}</td>
            </tr>
            <tr>
                <td><strong>Fecha documento:</strong></td>
                <td>{{ $accountReceivable->document_date }}</td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td>{{ strtoupper($accountReceivable->status) }}</td>
            </tr>
        </table>
    </div>

    <h2>Resumen</h2>
    <table>
        <thead>
            <tr>
                <th>Monto original</th>
                <th>Pagado</th>
                <th>Saldo pendiente</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Q{{ number_format((float)$accountReceivable->original_amount_q, 2) }}</td>
                <td>Q{{ number_format((float)$accountReceivable->paid_amount_q, 2) }}</td>
                <td>Q{{ number_format((float)$accountReceivable->balance_q, 2) }}</td>
                <td>{{ strtoupper($accountReceivable->status) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Historial de abonos</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Notas</th>
                <th>Registrado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accountReceivable->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at }}</td>
                    <td>Q{{ number_format((float)$payment->amount_q, 2) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->notes ?? '—' }}</td>
                    <td>{{ $payment->creator?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay abonos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>