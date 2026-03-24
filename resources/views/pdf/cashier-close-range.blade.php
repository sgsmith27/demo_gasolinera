<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, h2, h3 { margin: 0 0 8px 0; }
        .card { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
    <h1>Cuadre despachador por rango</h1>
    <div style="margin-bottom: 15px;">Desde: {{ $from->toDateString() }} | Hasta: {{ $to->toDateString() }}</div>

    @foreach($summaryByUser as $user)
        @php
            $payments = $paymentsByUser->where('user_id', $user->user_id);
            $fuels = $fuelsByUser->where('user_id', $user->user_id);
        @endphp

        <div class="card">
            <h2>{{ $user->user_name }}</h2>
            <div>
                Ventas: {{ $user->sales_count }} |
                Total Q: Q{{ number_format((float)$user->total_q, 2) }} |
                Galones: {{ number_format((float)$user->total_gallons, 3) }} gal
            </div>

            <h3 style="margin-top:10px;">Métodos de pago</h3>
            <table>
                <thead><tr><th>Método</th><th>Ventas</th><th>Total Q</th><th>Galones</th></tr></thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr>
                            <td>{{ $p->payment_method }}</td>
                            <td>{{ $p->sales_count }}</td>
                            <td>Q{{ number_format((float)$p->total_q, 2) }}</td>
                            <td>{{ number_format((float)$p->total_gallons, 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3>Por combustible</h3>
            <table>
                <thead><tr><th>Combustible</th><th>Ventas</th><th>Total Q</th><th>Galones</th></tr></thead>
                <tbody>
                    @foreach($fuels as $f)
                        <tr>
                            <td>{{ $f->fuel_name }}</td>
                            <td>{{ $f->sales_count }}</td>
                            <td>Q{{ number_format((float)$f->total_q, 2) }}</td>
                            <td>{{ number_format((float)$f->total_gallons, 3) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>