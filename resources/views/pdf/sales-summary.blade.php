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
    <h1>Resumen por rango</h1>
    <div class="mb">Desde: {{ $from->toDateString() }} | Hasta: {{ $to->toDateString() }}</div>

    <div class="mb">
        <strong>Totales:</strong><br>
        Ventas: {{ $totals->total_sales }} |
        Total Q: Q{{ number_format((float)$totals->total_q, 2) }} |
        Total galones: {{ number_format((float)$totals->total_gallons, 3) }} gal
    </div>

    <h2>Por bomba</h2>
    <table class="mb">
        <thead><tr><th>Bomba</th><th>Ventas</th><th>Total Q</th><th>Galones</th></tr></thead>
        <tbody>
            @foreach($byPump as $r)
                <tr>
                    <td>{{ $r->pump_code }}</td>
                    <td>{{ $r->sales_count }}</td>
                    <td>Q{{ number_format((float)$r->total_q, 2) }}</td>
                    <td>{{ number_format((float)$r->total_gallons, 3) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Por despachador</h2>
    <table class="mb">
        <thead><tr><th>Despachador</th><th>Ventas</th><th>Total Q</th><th>Galones</th></tr></thead>
        <tbody>
            @foreach($byUser as $r)
                <tr>
                    <td>{{ $r->user_name }}</td>
                    <td>{{ $r->sales_count }}</td>
                    <td>Q{{ number_format((float)$r->total_q, 2) }}</td>
                    <td>{{ number_format((float)$r->total_gallons, 3) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Por combustible</h2>
    <table>
        <thead><tr><th>Combustible</th><th>Ventas</th><th>Total Q</th><th>Galones</th></tr></thead>
        <tbody>
            @foreach($byFuel as $r)
                <tr>
                    <td>{{ $r->fuel_name }}</td>
                    <td>{{ $r->sales_count }}</td>
                    <td>Q{{ number_format((float)$r->total_q, 2) }}</td>
                    <td>{{ number_format((float)$r->total_gallons, 3) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>