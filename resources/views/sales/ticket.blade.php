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

        .qr-box {
            text-align: center;
            margin-top: 6px;
        }

        .qr-box img {
            width: 90px;
            height: 90px;
        }

        .qr-caption {
            font-size: 8px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="center mb-2">
            <div class="title">GASOLINERA D-R SAN JUAN</div>
            <div class="subtitle">Factura Electrónica</div>
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
            @php
                $felDoc = $sale->latestFelDocument;
                $ticketReceiverName = $felDoc?->receiver_name ?? $sale->customer?->name ?? 'CONSUMIDOR FINAL';
                $ticketReceiverTaxid = $felDoc?->receiver_taxid ?? $sale->customer?->nit ?? 'CF';

                $qrText = null;
                $qrUrl = null;

                if ($felDoc && in_array($felDoc->fel_status, ['certified', 'cancelled'])) {
                    $qrText = implode("\n", array_filter([
                        'UUID: ' . ($felDoc->uuid ?? '—'),
                        'Serie: ' . ($felDoc->series ?? '—'),
                        'Número: ' . ($felDoc->number ?? '—'),
                        'Receptor: ' . $ticketReceiverName,
                        'NIT/CUI: ' . $ticketReceiverTaxid,
                        'Total: Q ' . number_format((float) $sale->total_amount_q, 2),
                        'Fecha: ' . optional($felDoc->issued_at)->format('d/m/Y H:i:s'),
                        'Estado FEL: ' . strtoupper($felDoc->fel_status ?? '—'),
                    ]));

                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($qrText);
                }
            @endphp

            <tr>
                <td class="text-left bold">Cliente:</td>
                <td class="text-right">{{ $ticketReceiverName }}</td>
            </tr>
            <tr>
                <td class="text-left bold">NIT/CUI:</td>
                <td class="text-right">{{ $ticketReceiverTaxid }}</td>
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
                    <td class="text-left bold">Serie:</td>
                    <td class="text-right">{{ $sale->latestFelDocument->series ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="text-left bold">Número:</td>
                    <td class="text-right">{{ $sale->latestFelDocument->number ?? '—' }}</td>
                </tr>
            </table>

            <div class="line"></div>

            <div class="center mb-1">
                <div class="small" style="word-break: break-all;">
                    {{ $sale->latestFelDocument->uuid ?? '—' }}
                </div>
            </div>
        @endif
        @if($qrUrl)
            <div class="line"></div>

            <div class="qr-box">
                <img src="{{ $qrUrl }}" alt="">
                <div class="qr-caption">QR FEL</div>
            </div>
        @endif

        <div class="line"></div>

        <div class="center mt-2">
            <div>¡Gracias por su compra!</div>
            <div class="small">Documento generado por el sistema</div>
        </div>
    </div>
</body>
</html>