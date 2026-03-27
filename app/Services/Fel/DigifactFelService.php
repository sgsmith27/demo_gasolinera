<?php

namespace App\Services\Fel;

use App\Models\FelConfig;
use App\Models\FelDocument;
use App\Models\FelEvent;
use App\Models\Sale;
use Digifact\Fel\DigifactClient;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Carbon\Carbon;

class DigifactFelService
{
    protected function getActiveConfig(): FelConfig
    {
        return FelConfig::query()
            ->where('is_active', true)
            ->latest('id')
            ->firstOrFail();
    }

    protected function makeClient(FelConfig $config): DigifactClient
    {
        return new DigifactClient([
            'taxid' => $config->taxid,
            'username' => $config->username,
            'password' => $config->password,
            'environment' => $config->environment,
            'seller_name' => $config->seller_name,
            'seller_address' => $config->seller_address,
            'afiliacion_iva' => $config->afiliacion_iva,
            'tipo_personeria' => $config->tipo_personeria,
        ]);
    }
   

    public function issueInvoiceFromSale(Sale $sale): FelDocument
    {
        $sale->load(['fuel', 'customer']);

        if ($sale->status !== 'active') {
            throw new \RuntimeException('Solo se pueden facturar ventas activas.');
        }

        $existingCertified = FelDocument::query()
            ->where('sale_id', $sale->id)
            ->where('doc_type', 'FACT')
            ->where('fel_status', 'certified')
            ->first();

        if ($existingCertified) {
            throw new \RuntimeException('La venta ya tiene una factura FEL certificada.');
        }

        $config = $this->getActiveConfig();
        $client = $this->makeClient($config);

        $buyer = $this->resolveBuyer($sale);

        $items = [[
            'description' => 'Venta combustible ' . ($sale->fuel?->name ?? 'Combustible'),
            'qty' => (float) $sale->gallons,
            'price' => (float) $sale->price_per_gallon,
            'type' => 'Bien',
        ]];

        $requestPayload = [
            'buyer' => $buyer,
            'items' => $items,
            'sale_id' => $sale->id,
        ];

        $document = FelDocument::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'doc_type' => 'FACT',
            'environment' => $config->environment,
            'fel_status' => 'pending',
            'receiver_taxid' => is_array($buyer) ? ($buyer['taxid'] ?? null) : $buyer,
            'receiver_name' => $sale->customer?->name ?? 'CONSUMIDOR FINAL',
            'total_amount_q' => $sale->total_amount_q,
            'request_payload' => $requestPayload,
            'created_by' => Auth::id(),
        ]);

        FelEvent::create([
            'fel_document_id' => $document->id,
            'event_type' => 'create',
            'description' => 'Documento FEL creado en estado pending',
            'payload' => $requestPayload,
        ]);

        try {
            $result = $client->invoice($buyer, $items);

            $responsePayload = [
                'authNumber' => $result->authNumber ?? null,
                'series' => $result->series ?? null,
                'number' => $result->number ?? null,
                'issuedAt' => $result->{'issuedAt'} ?? null,
                'xml' => $result->xml ?? null,
                'pdf' => $result->pdf ?? null,
                'html' => $result->html ?? null,
                'raw' => $result,
            ];

            $document->update([
                'fel_status' => 'certified',
                'uuid' => $result->authNumber ?? null,
                'series' => $result->series ?? null,
                'number' => $result->number ?? null,
                'issued_at' => isset($result->{'issuedAt'})
    ? \Carbon\Carbon::parse($result->{'issuedAt'})
    : now(),
                'response_payload' => $responsePayload,
                'xml' => $result->xml ?? null,
                'pdf' => $result->pdf ?? null,
                'html' => $result->html ?? null,
                'error_message' => null,
            ]);

            FelEvent::create([
                'fel_document_id' => $document->id,
                'event_type' => 'success',
                'description' => 'Documento FEL certificado correctamente',
                'response' => $responsePayload,
            ]);

            return $document->fresh();
        } catch (Throwable $e) {
            $document->update([
                'fel_status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            FelEvent::create([
                'fel_document_id' => $document->id,
                'event_type' => 'error',
                'description' => 'Error al certificar FEL',
                'response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }

    protected function resolveBuyer(Sale $sale): string|array
    {
        if (! $sale->customer || ! $sale->customer->nit) {
            return 'CF';
        }

        return $sale->customer->nit;
    }

    public function cancelDocument(FelDocument $document, string $reason): FelDocument
    {
        if ($document->fel_status !== 'certified') {
            throw new \RuntimeException('Solo se pueden anular documentos FEL certificados.');
        }

        $config = $this->getActiveConfig();
        $client = $this->makeClient($config);

        $buyer = $document->receiver_taxid ?: 'CF';
        $issuedAt = $document->issued_at
            ? $document->issued_at->format('Y-m-d H:i:s')
            : now()->format('Y-m-d H:i:s');

        $payload = [
            'uuid' => $document->uuid,
            'buyer' => $buyer,
            'issued_at' => $issuedAt,
            'reason' => $reason,
        ];

        FelEvent::create([
            'fel_document_id' => $document->id,
            'event_type' => 'cancel',
            'description' => 'Solicitud de anulación FEL',
            'payload' => $payload,
        ]);

        try {
            $result = $client->cancel($document->uuid, $buyer, $issuedAt, $reason);

            $responsePayload = [
                'raw' => $result,
            ];

            $document->update([
                'fel_status' => 'cancelled',
                'response_payload' => array_merge($document->response_payload ?? [], [
                    'cancel_response' => $responsePayload,
                ]),
                'error_message' => null,
            ]);

            FelEvent::create([
                'fel_document_id' => $document->id,
                'event_type' => 'success',
                'description' => 'Documento FEL anulado correctamente',
                'response' => $responsePayload,
            ]);

            return $document->fresh();
        } catch (\Throwable $e) {
            $document->update([
                'error_message' => $e->getMessage(),
            ]);

            FelEvent::create([
                'fel_document_id' => $document->id,
                'event_type' => 'error',
                'description' => 'Error al anular FEL',
                'response' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }
}