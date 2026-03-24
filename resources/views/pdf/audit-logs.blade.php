<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { margin-bottom: 10px; }
        .mb { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
    <h1>Bitácora de anulaciones</h1>

    <div class="mb">
        Desde: {{ $from }} |
        Hasta: {{ $to }} |
        Módulo: {{ $moduleLabel ?? 'Todos' }} |
        Acción: {{ $actionLabel ?? 'Todas' }} |
        Usuario: {{ $userLabel ?? 'Todos' }}
    </div>




    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>Entidad</th>
                <th>ID</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->event_at }}</td>
                    <td>{{ $log->user?->name ?? '—' }}</td>
                    <td>
                        @if(($log->module ?? '') === 'sales')
                            Ventas
                        @elseif(($log->module ?? '') === 'fuel_deliveries')
                            Abastecimientos
                        @elseif(($log->module ?? '') === 'expenses')
                            Gastos
                        @else
                            {{ $log->module }}
                        @endif
                    </td>

                    <td>
                        @if(($log->action ?? '') === 'void')
                            Anulación
                        @elseif(($log->action ?? '') === 'adjust')
                            Ajuste
                        @elseif(($log->action ?? '') === 'create')
                            Creación
                        @elseif(($log->action ?? '') === 'update')
                            Actualización
                        @else
                            {{ $log->action }}
                        @endif
                    </td>

                    <td>{{ $log->entity_type }}</td>
                    <td>{{ $log->entity_id }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay anulaciones registradas en el rango indicado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>