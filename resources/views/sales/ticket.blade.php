<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Venta #{{ $sale->id }}</title>
    <style>
        @page {
            margin: 8px 8px 12px 8px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
        }

        .ticket {
            width: 100%;
        }

        .center {
            text-align: center;
        }

        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }

        .title {
            font-size: 10px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 9px;
        }

       .line {
        border-top: 1px dashed #000;
        margin: 4px 0;}

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        .small {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center mb-2">
            <div class="title">ESTACIÓN DE SERVICIO</div>
            <div class="subtitle">Ticket de venta</div>
        </div>

        <div class="line"></div>

        <table class="mb-1">
            <tr>
                <td class="text-left bold">Venta:</td>
                <td class="text-right">#{{ $sale->id }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Fecha:</td>
                <td class="text-right">{{ optional($sale->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Usuario:</td>
                <td class="text-right">{{ $sale->user?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Turno:</td>
                <td class="text-right">#{{ $sale->shift?->id ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Bomba:</td>
                <td class="text-right">{{ $sale->nozzle?->name ?? $sale->nozzle?->number ?? '—' }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table class="mb-1">
            <tr>
                <td class="text-left bold">Combustible:</td>
                <td class="text-right">{{ $sale->fuel?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Galones:</td>
                <td class="text-right">{{ number_format((float) $sale->gallons, 3) }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Precio/gal:</td>
                <td class="text-right">Q {{ number_format((float) $sale->price_per_gallon, 3) }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Total:</td>
                <td class="text-right bold">Q {{ number_format((float) $sale->total_amount_q, 2) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table class="mb-1">
            <tr>
                <td class="text-left bold">Cliente:</td>
                <td class="text-right">{{ $sale->customer?->name ?? 'CONSUMIDOR FINAL' }}</td>
            </tr>
            <tr>
                <td class="text-left bold">NIT:</td>
                <td class="text-right">{{ $sale->customer?->nit ?? 'CF' }}</td>
            </tr>
            <tr>
                <td class="text-left bold">Pago:</td>
                <td class="text-right">{{ strtoupper($sale->payment_method ?? '—') }}</td>
            </tr>
        </table>

        @if($sale->latestFelDocument && in_array($sale->latestFelDocument->fel_status, ['certified', 'cancelled']))
            <div class="line"></div>

            <table class="mb-1">
                <tr>
                    <td class="text-left bold">FEL:</td>
                    <td class="text-right">{{ strtoupper($sale->latestFelDocument->fel_status) }}</td>
                </tr>
                <tr>
                    <td class="text-left bold">UUID:</td>
                    <td class="text-right small">{{ $sale->latestFelDocument->uuid ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Serie:</td>
                    <td class="text-right">{{ $sale->latestFelDocument->series ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Número:</td>
                    <td class="text-right">{{ $sale->latestFelDocument->number ?? '—' }}</td>
                </tr>
            </table>
        @endif

        <div class="line"></div>

        <div class="center mt-2">
            <div>¡Gracias por su compra!</div>
            <div class="small">Documento generado por el sistema</div>
        </div>
    </div>
</body>
</html>