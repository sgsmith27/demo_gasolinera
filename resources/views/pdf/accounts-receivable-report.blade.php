<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { margin-bottom: 10px; }
        .mb { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; vertical-align: top; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
    <h1>Reporte de cuentas por cobrar</h1>

    <div class="mb">
        Desde: {{ $from }} |
        Hasta: {{ $to }} |
        Cliente: {{ $customerLabel }} |
        Estado: {{ $statusLabel }}
    </div>

    <div class="mb">
        Documentos: {{ $summary->total_items ?? 0 }} |
        Original: Q{{ number_format((float)($summary->original_total_q ?? 0), 2) }} |
        Pagado: Q{{ number_format((float)($summary->paid_total_q ?? 0), 2) }} |
        Saldo: Q{{ number_format((float)($summary->balance_total_q ?? 0), 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Venta</th>
                <th>Original</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $account)
                <tr>
                    <td>{{ $account->document_date }}</td>
                    <td>{{ $account->customer?->name ?? '—' }}</td>
                    <td>#{{ $account->sale_id }}</td>
                    <td>Q{{ number_format((float)$account->original_amount_q, 2) }}</td>
                    <td>Q{{ number_format((float)$account->paid_amount_q, 2) }}</td>
                    <td>Q{{ number_format((float)$account->balance_q, 2) }}</td>
                    <td>{{ strtoupper($account->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay registros.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>