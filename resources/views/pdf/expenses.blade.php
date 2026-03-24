<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2 { margin: 0 0 10px 0; }
        .mb { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
    <h1>Reporte de gastos operativos</h1>
    <div class="mb">
        Desde: {{ $from ?? '—' }} |
        Hasta: {{ $to ?? '—' }}
    </div>

    <div class="mb">
        <strong>Total general:</strong>
        Q{{ number_format((float)$grandTotal->total_q, 2) }}
    </div>

    <h2>Totales por categoría</h2>
    <table class="mb">
        <thead>
            <tr>
                <th>Categoría</th>
                <th>Registros</th>
                <th>Total Q</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalsByCategory as $row)
                <tr>
                    <td>{{ $row->category }}</td>
                    <td>{{ $row->items_count }}</td>
                    <td>Q{{ number_format((float)$row->total_q, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Detalle de gastos</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date }}</td>
                    <td>{{ $expense->category }}</td>
                    <td>{{ $expense->concept }}</td>
                    <td>Q{{ number_format((float)$expense->amount_q, 2) }}</td>
                    <td>{{ $expense->notes ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>