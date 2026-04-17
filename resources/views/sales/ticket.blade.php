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

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .detail-table th,
        .detail-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8px;
        }

        .detail-col-qty {
            width: 18%;
        }

        .detail-col-desc {
            width: 34%;
        }

        .detail-col-price {
            width: 22%;
        }

        .detail-col-total {
            width: 26%;
        }

        .detail-desc {
            word-wrap: break-word;
            word-break: break-word;
        }

        .detail-num {
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @php
    $felDoc = $sale->latestFelDocument;
    $ticketReceiverName = $felDoc?->receiver_name ?? $sale->customer?->name ?? 'CONSUMIDOR FINAL';
    $ticketReceiverTaxid = $felDoc?->receiver_taxid ?? $sale->customer?->nit ?? 'CF';

    $activeFelConfig = \App\Models\FelConfig::query()->where('is_active', true)->first();

    $emitterTaxid = preg_replace('/\D+/', '', (string) ($activeFelConfig->taxid ?? ''));
    $emitterName = $activeFelConfig->seller_name ?? 'EMISOR';
    $emitterAddress = $activeFelConfig->seller_address ?? 'DIRECCIÓN NO CONFIGURADA';

    $receiverForSat = strtoupper(trim((string) $ticketReceiverTaxid));
    $receiverForSat = $receiverForSat !== '' ? $receiverForSat : 'CF';

    $totalForSat = number_format((float) $sale->total_amount_q, 2, '.', '');

    $qrUrl = null;

    if ($felDoc && in_array($felDoc->fel_status, ['certified', 'cancelled']) && !empty($felDoc->uuid)) {
        $satVerifyUrl = 'https://felpub.c.sat.gob.gt/verificador-web/publico/vistas/verificacionDte.jsf'
            . '?tipo=autorizacion'
            . '&numero=' . urlencode((string) $felDoc->uuid)
            . '&emisor=' . urlencode($emitterTaxid)
            . '&receptor=' . urlencode($receiverForSat)
            . '&monto=' . urlencode($totalForSat);

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=' . urlencode($satVerifyUrl);
    }
@endphp

    <div class="ticket">
    <div class="center mb-2">
        <div class="title">FACTURA ELECTRONICA</div>
        <div class="title">{{ strtoupper($sale->payment_method ?? 'EFECTIVO') }}</div>
        <div class="mb-1"></div>
        <div class="title">FEL</div>
        <div class="subtitle">Datos del Emisor</div>
        <div>NIT: {{ $emitterTaxid ?: '—' }}</div>
        <div class="bold">{{ strtoupper($emitterName) }}</div>
        <div>{{ strtoupper($emitterAddress) }}</div>
    </div>

    <div class="line"></div>

    <div class="mb-2">
        <div class="bold center">Documento Tributario Electrónico</div>
        <div>Serie: {{ $felDoc->series ?? '—' }}</div>
        <div>No de DTE: {{ $felDoc->number ?? '—' }}</div>
    </div>

    <div class="line"></div>

    <div class="center mb-1 bold">DATOS DEL COMPRADOR</div>
    <div class="center">Fecha: {{ optional($sale->created_at)->format('d-m-Y') }}</div>
    <div class="center">NIT: {{ $ticketReceiverTaxid }}</div>
    <div class="center bold">{{ strtoupper($ticketReceiverName) }}</div>

    <div class="line"></div>

    <div class="center mb-1 bold">DETALLE DE LA FACTURA</div>

    <table class="detail-table mb-1">
        <thead>
            <tr>
                <th class="detail-col-qty text-left">CANT</th>
                <th class="detail-col-desc text-left">DESCRIP.</th>
                <th class="detail-col-price detail-num">PRECIO</th>
                <th class="detail-col-total detail-num">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">
                    {{ number_format((float) $sale->gallons, 3) }}
                </td>
                <td class="detail-desc text-left">
                    {{ strtoupper($sale->fuel?->name ?? 'COMBUSTIBLE') }}
                </td>
                <td class="detail-num">
                    {{ number_format((float) $sale->price_per_gallon, 2) }}
                </td>
                <td class="detail-num">
                    {{ number_format((float) $sale->total_amount_q, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="line"></div>

    <table class="mb-1">
        <tr>
            <td class="text-left bold">BASE:</td>
            <td class="text-right">Q{{ number_format((float) $sale->taxable_base_q, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left bold">IVA:</td>
            <td class="text-right">Q{{ number_format((float) $sale->vat_amount_q, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left bold">IMPUESTO IDP:</td>
            <td class="text-right">Q{{ number_format((float) $sale->idp_amount_q, 2) }}</td>
        </tr>
        <tr>
            <td class="text-left bold">TOTAL:</td>
            <td class="text-right bold">Q{{ number_format((float) $sale->total_amount_q, 2) }}</td>
        </tr>
    </table>

    <div class="center mb-2">
        <div>Moneda utilizada: Quetzal</div>
    </div>

    <div class="line"></div>

    <div class="center mb-1 bold">NUMERO DE AUTORIZACION</div>
    <div class="center small" style="word-break: break-all;">
        {{ $felDoc->uuid ?? '—' }}
    </div>
    <div class="center">
        Fecha Certificación: {{ optional($felDoc?->issued_at)->format('Y-m-d\TH:i:s') ?? '—' }}
    </div>
    <div class="center">Sujeto a pagos trimestrales ISR</div>

    <div class="line"></div>

    <div class="center mb-1 bold">Datos del Certificador</div>
    <div class="center">NIT: 7745482-0</div>
    <div class="center bold">DIGIFACT SERVICIOS, S.A.</div>

    @if($qrUrl)
        <div class="line"></div>
        <div class="qr-box">
            <img src="{{ $qrUrl }}" alt="">
            <div class="qr-caption">Verificación SAT</div>
        </div>
    @endif
</div>
</body>
</html>