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
    <h1>Reporte de turnos</h1>

    <div class="mb">
        Desde: {{ $from }} |
        Hasta: {{ $to }} |
        Despachador: {{ $userLabel ?? 'Todos' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Despachador</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Asignación</th>
                <th>Fondo inicial</th>
                <th>Esperado</th>
                <th>Entregado</th>
                <th>Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $shift)
                <tr>
                    <td>{{ $shift->id }}</td>
                    <td>{{ $shift->user?->name ?? '—' }}</td>
                    <td>{{ $shift->started_at }}</td>
                    <td>{{ $shift->ended_at ?? '—' }}</td>
                    <td>{{ strtoupper($shift->status) }}</td>
                    <td>
                        @if($shift->assignment_mode === 'fixed')
                            {{ $shift->pump?->code ?? 'Bomba fija' }}
                        @else
                            Libre
                        @endif
                    </td>
                    <td>Q{{ number_format((float)$shift->opening_cash_q, 2) }}</td>
                    <td>{{ $shift->expected_cash_q !== null ? 'Q'.number_format((float)$shift->expected_cash_q, 2) : '—' }}</td>
                    <td>{{ $shift->delivered_cash_q !== null ? 'Q'.number_format((float)$shift->delivered_cash_q, 2) : '—' }}</td>
                    <td>{{ $shift->cash_difference_q !== null ? 'Q'.number_format((float)$shift->cash_difference_q, 2) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No hay turnos en el rango seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>