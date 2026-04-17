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
    </style>
</head>
<body>
    <h1>Balance operativo</h1>

    <div class="mb">
        Desde: {{ $from }} |
        Hasta: {{ $to }}
    </div>

    <div class="mb">
        Ventas: Q{{ number_format((float)($sales->total_q ?? 0), 2) }} |
        Abastecimientos: Q{{ number_format((float)($fuelDeliveries->total_q ?? 0), 2) }} |
        Cobros CxC: Q{{ number_format((float)($receivableCollections->total_q ?? 0), 2) }} |
        Gastos: Q{{ number_format((float)($expenses->total_q ?? 0), 2) }} |
        Pagos CxP: Q{{ number_format((float)($payablePayments->total_q ?? 0), 2) }} |
        Balance final: Q{{ number_format((float)$finalBalance, 2) }}
    </div>

    <div class="mb">
        Pendiente por cobrar: Q{{ number_format((float)($pendingReceivables->total_q ?? 0), 2) }} |
        Pendiente por pagar: Q{{ number_format((float)($pendingPayables->total_q ?? 0), 2) }}
    </div>

    <h2>Resumen del período</h2>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Cantidad</th>
                <th>Total Q</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ventas</td>
                <td>{{ $sales->total_sales ?? 0 }}</td>
                <td>Q{{ number_format((float)($sales->total_q ?? 0), 2) }}</td>
            </tr>
                <tr>
                    <td>Abastecimientos</td>
                    <td>{{ $fuelDeliveries->total_items ?? 0 }}</td>
                    <td>Q{{ number_format((float)($fuelDeliveries->total_q ?? 0), 2) }}</td>
            <tr>
                <td>Gastos</td>
                <td>{{ $expenses->total_items ?? 0 }}</td>
                <td>Q{{ number_format((float)($expenses->total_q ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Cobros CxC</td>
                <td>{{ $receivableCollections->total_items ?? 0 }}</td>
                <td>Q{{ number_format((float)($receivableCollections->total_q ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Pagos CxP</td>
                <td>{{ $payablePayments->total_items ?? 0 }}</td>
                <td>Q{{ number_format((float)($payablePayments->total_q ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Ventas por método de pago</h2>
    <table>
        <thead>
            <tr>
                <th>Método</th>
                <th>Ventas</th>
                <th>Total Q</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salesByPayment as $row)
                <tr>
                    <td>{{ $row->payment_method }}</td>
                    <td>{{ $row->total_sales }}</td>
                    <td>Q{{ number_format((float)$row->total_q, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay ventas en el rango.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
