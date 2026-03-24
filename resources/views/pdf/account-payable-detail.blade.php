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
    <h1>Detalle de cuenta por pagar #{{ $accountPayable->id }}</h1>

    <div class="mb">
        <table class="grid">
            <tr><td><strong>Proveedor:</strong></td><td>{{ $accountPayable->supplier?->name ?? '—' }}</td></tr>
            <tr><td><strong>NIT:</strong></td><td>{{ $accountPayable->supplier?->nit ?? '—' }}</td></tr>
            <tr><td><strong>Fecha documento:</strong></td><td>{{ $accountPayable->document_date }}</td></tr>
            <tr><td><strong>Documento:</strong></td><td>{{ $accountPayable->document_no ?? '—' }}</td></tr>
            <tr><td><strong>Categoría:</strong></td><td>{{ strtoupper($accountPayable->category) }}</td></tr>
            <tr><td><strong>Descripción:</strong></td><td>{{ $accountPayable->description }}</td></tr>
            <tr><td><strong>Estado:</strong></td><td>{{ strtoupper($accountPayable->status) }}</td></tr>
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
                <td>Q{{ number_format((float)$accountPayable->original_amount_q, 2) }}</td>
                <td>Q{{ number_format((float)$accountPayable->paid_amount_q, 2) }}</td>
                <td>Q{{ number_format((float)$accountPayable->balance_q, 2) }}</td>
                <td>{{ strtoupper($accountPayable->status) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Historial de pagos</h2>
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
            @forelse($accountPayable->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at }}</td>
                    <td>Q{{ number_format((float)$payment->amount_q, 2) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->notes ?? '—' }}</td>
                    <td>{{ $payment->creator?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay pagos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>