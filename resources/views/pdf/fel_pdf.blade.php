<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte FEL</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .meta {
            margin-bottom: 12px;
            font-size: 10px;
        }

        .summary {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #d1d5db;
            padding: 5px;
            vertical-align: top;
        }

        table.report th {
            background: #f3f4f6;
            text-align: left;
        }

        .small {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <h1>Reporte FEL (Libro de Ventas)</h1>

    <div class="meta">
        <strong>Desde:</strong> {{ $from }}
        &nbsp;&nbsp;
        <strong>Hasta:</strong> {{ $to }}
        &nbsp;&nbsp;
        <strong>Estado:</strong> {{ $status ?: 'Todos' }}
    </div>

    <table class="summary">
        <tr>
            <td><strong>Documentos</strong><br>{{ $totals['count'] }}</td>
            <td><strong>Certificados</strong><br>{{ $totals['certified_count'] }}</td>
            <td><strong>Anulados</strong><br>{{ $totals['cancelled_count'] }}</td>
            <td><strong>Con error</strong><br>{{ $totals['error_count'] }}</td>
            <td><strong>Monto total</strong><br>Q{{ number_format((float) $totals['total_amount'], 2) }}</td>
            <td><strong>Base imponible</strong><br>Q{{ number_format((float) $totals['taxable_base'], 2) }}</td>
            <td><strong>IVA reportado</strong><br>Q{{ number_format((float) $totals['reported_vat_amount'], 2) }}</td>
            <td><strong>IDP reportado</strong><br>Q{{ number_format((float) $totals['reported_idp_amount'], 2) }}</td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Venta</th>
                <th>UUID</th>
                <th>Serie</th>
                <th>Número</th>
                <th>Cliente</th>
                <th>NIT/CUI</th>
                <th>Total</th>
                <th>Base</th>
                <th>IVA</th>
                <th>IDP</th>
                <th>Estado</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $doc)
                <tr>
                    <td>{{ $doc->issued_at }}</td>
                    <td>#{{ $doc->sale_id }}</td>
                    <td class="small">{{ $doc->uuid }}</td>
                    <td>{{ $doc->series }}</td>
                    <td>{{ $doc->number }}</td>
                    <td>{{ $doc->receiver_name ?? 'CONSUMIDOR FINAL' }}</td>
                    <td>{{ $doc->receiver_taxid ?? 'CF' }}</td>
                    <td>Q{{ number_format((float) ($doc->total_amount_q ?? $doc->sale->total_amount_q ?? 0), 2) }}</td>
                    <td>Q{{ number_format((float) ($doc->taxable_base_q ?? 0), 2) }}</td>
                    <td>Q{{ number_format((float) ($doc->vat_amount_q ?? 0), 2) }}</td>
                    <td>Q{{ number_format((float) ($doc->idp_amount_q ?? 0), 2) }}</td>
                    <td>{{ strtoupper($doc->fel_status) }}</td>
                    <td>{{ $doc->sale->user->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>