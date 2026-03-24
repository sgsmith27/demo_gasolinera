<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1, h2 { margin: 0 0 10px 0; }
        .mb { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f3f3; }
        .grid td { border: none; padding: 3px 0; }
    </style>
</head>
<body>
    <h1>Detalle de turno #{{ $workShift->id }}</h1>

    <div class="mb">
        <table class="grid">
            <tr><td><strong>Despachador:</strong></td><td>{{ $workShift->user?->name ?? '—' }}</td></tr>
            <tr><td><strong>Inicio:</strong></td><td>{{ $workShift->started_at }}</td></tr>
            <tr><td><strong>Fin:</strong></td><td>{{ $workShift->ended_at ?? '—' }}</td></tr>
            <tr><td><strong>Estado:</strong></td><td>{{ strtoupper($workShift->status) }}</td></tr>
            <tr>
                <td><strong>Asignación:</strong></td>
                <td>
                    @if($workShift->assignment_mode === 'fixed')
                        Bomba fija — {{ $workShift->pump?->code ?? '—' }}
                    @else
                        Libre
                    @endif
                </td>
            </tr>
            <tr><td><strong>Fondo inicial:</strong></td><td>Q{{ number_format((float)$workShift->opening_cash_q, 2) }}</td></tr>
            <tr><td><strong>Efectivo esperado:</strong></td><td>Q{{ number_format((float)$expectedCash, 2) }}</td></tr>
            <tr><td><strong>Efectivo entregado:</strong></td><td>{{ $workShift->delivered_cash_q !== null ? 'Q'.number_format((float)$workShift->delivered_cash_q, 2) : '—' }}</td></tr>
            <tr><td><strong>Diferencia:</strong></td><td>{{ $workShift->cash_difference_q !== null ? 'Q'.number_format((float)$workShift->cash_difference_q, 2) : '—' }}</td></tr>
        </table>
    </div>

    <h2>Resumen</h2>
    <table>
        <thead>
            <tr>
                <th>Ventas activas</th>
                <th>Total vendido</th>
                <th>Galones vendidos</th>
                <th>Ventas en efectivo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $salesSummary->total_sales ?? 0 }}</td>
                <td>Q{{ number_format((float)($salesSummary->total_q ?? 0), 2) }}</td>
                <td>{{ number_format((float)($salesSummary->total_gallons ?? 0), 3) }}</td>
                <td>Q{{ number_format((float)$cashSales, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Métodos de pago</h2>
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
                    <td colspan="3">Sin ventas en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas del turno</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Combustible</th>
                <th>Galones</th>
                <th>Total Q</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestSales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->sold_at }}</td>
                    <td>{{ $sale->fuel?->name ?? '—' }}</td>
                    <td>{{ number_format((float)$sale->gallons, 3) }}</td>
                    <td>Q{{ number_format((float)$sale->total_amount_q, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay ventas registradas en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>