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
    <h1>Reporte de abastecimientos</h1>
    <div class="mb">
        Desde: {{ $from ?? '—' }} |
        Hasta: {{ $to ?? '—' }}
    </div>

    <div class="mb">
        <strong>Totales activos:</strong><br>
        Registros: {{ $totals->total_items ?? 0 }} |
        Galones: {{ number_format((float)($totals->total_gallons ?? 0), 3) }} |
        Costo total Q: Q{{ number_format((float)($totals->total_cost_q ?? 0), 2) }}
    </div>

    <h2>Detalle</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Combustible</th>
                <th>Tanque</th>
                <th>Galones</th>
                <th>Costo Q</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $delivery)
                <tr>
                    <td>{{ $delivery->id }}</td>
                    <td>{{ $delivery->delivered_at }}</td>
                    <td>{{ $delivery->user_name }}</td>
                    <td>{{ $delivery->fuel_name }}</td>
                    <td>#{{ $delivery->tank_id }}</td>
                    <td>{{ number_format((float)$delivery->gallons, 3) }}</td>
                    <td>{{ $delivery->total_cost_q === null ? '' : 'Q'.number_format((float)$delivery->total_cost_q, 2) }}</td>
                    <td>{{ $delivery->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>