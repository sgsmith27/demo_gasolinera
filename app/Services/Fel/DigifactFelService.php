<?php

namespace App\Services\Fel;

use App\Models\FelConfig;
use App\Models\FelDocument;
use App\Models\FelEvent;
use App\Models\Sale;
use Carbon\Carbon;
use Digifact\Fel\DigifactClient;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class DigifactFelService
{
    public function __construct(
        protected FelPayloadValidator $validator,
        protected FelErrorMapper $errorMapper,
    ) {
    }

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
        $config = $this->getActiveConfig();
        $this->validator->validateSaleInvoice($sale, $config);

        [$document, $buyer, $items, $requestPayload] = DB::transaction(function () use ($sale, $config) {
            $lockedSale = Sale::query()
                ->whereKey($sale->id)
                ->with(['fuel', 'customer'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->validator->validateSaleInvoice($lockedSale, $config);

            $existingCertified = FelDocument::query()
                ->where('sale_id', $lockedSale->id)
                ->where('doc_type', 'FACT')
                ->where('fel_status', 'certified')
                ->lockForUpdate()
                ->first();

            if ($existingCertified) {
                throw new \RuntimeException('La venta ya tiene una factura FEL certificada.');
            }

            $existingPending = FelDocument::query()
                ->where('sale_id', $lockedSale->id)
                ->where('doc_type', 'FACT')
                ->where('fel_status', 'pending')
                ->lockForUpdate()
                ->first();

            if ($existingPending) {
                throw new \RuntimeException('Ya existe una certificación FEL en proceso para esta venta.');
            }

            $buyer = $this->resolveBuyer($lockedSale);
            $items = $this->buildItems($lockedSale);

            $requestPayload = [
                'buyer' => $buyer,
                'items' => $items,
                'sale_id' => $lockedSale->id,
                'customer_id' => $lockedSale->customer_id,
                'environment' => $config->environment,
            ];

            try {
                $document = FelDocument::create([
                    'sale_id' => $lockedSale->id,
                    'customer_id' => $lockedSale->customer_id,
                    'doc_type' => 'FACT',
                    'environment' => $config->environment,
                    'fel_status' => 'pending',
                    'receiver_taxid' => is_array($buyer) ? ($buyer['taxid'] ?? null) : $buyer,
                    'receiver_name' => $lockedSale->customer?->name ?? 'CONSUMIDOR FINAL',
                    'total_amount_q' => $lockedSale->total_amount_q,
                    'request_payload' => $requestPayload,
                    'created_by' => Auth::id(),
                ]);
            } catch (QueryException $e) {
                throw new \RuntimeException('Ya existe una certificación FEL activa para esta venta.');
            }

            $this->logEvent(
                $document,
                'create',
                'Documento FEL creado en estado pending',
                payload: $requestPayload
            );

            return [$document, $buyer, $items, $requestPayload];
        });

        $client = $this->makeClient($config);

        try {
            $result = $client->invoice($buyer, $items);

            $responsePayload = [
            'authNumber' => $result->authNumber ?? null,
            'series' => $result->series ?? null,
            'number' => $result->number ?? null,
            'issueDateTime' => $result->issueDateTime ?? null,
            'xml_base64' => $result->raw['responseData1'] ?? null,
            'html_base64' => $result->raw['responseData2'] ?? null,
            'pdf_base64' => $result->raw['responseData3'] ?? null,
            'raw' => $result->raw ?? null,
        ];

            $document->update([
                'fel_status' => 'certified',
                'uuid' => $result->authNumber ?? null,
                'series' => $result->series ?? null,
                'number' => $result->number ?? null,
                'issued_at' => $this->normalizeIssuedAt($result->issueDateTime ?? ($result->raw['issuedTimeStamp'] ?? null)),
                'response_payload' => $responsePayload,
                'xml' => $result->raw['responseData1'] ?? null,
                'html' => $result->raw['responseData2'] ?? null,
                'pdf' => $result->raw['responseData3'] ?? null,
                'error_message' => null,
            ]);

            $this->logEvent(
                $document->fresh(),
                'success',
                'Documento FEL certificado correctamente',
                payload: $requestPayload,
                response: $responsePayload
            );

            return $document->fresh();
        } catch (Throwable $e) {
            $mapped = $this->errorMapper->map($e);

            $document->update([
                'fel_status' => 'error',
                'error_message' => $mapped['message'],
                'response_payload' => array_merge($document->response_payload ?? [], [
                    'error' => $mapped,
                ]),
            ]);

            $this->logEvent(
                $document->fresh(),
                'error',
                'Error al certificar FEL',
                payload: $requestPayload,
                response: ['error' => $mapped]
            );

            throw new \RuntimeException($mapped['message'].' '.$mapped['hint'], previous: $e);
        }
    }

    protected function resolveBuyer(Sale $sale): string|array
    {
        if (! $sale->customer) {
            return 'CF';
        }

        $nit = trim((string) ($sale->customer->nit ?? ''));

        if ($nit === '') {
            return 'CF';
        }

        return strtoupper(str_replace([' ', '-'], '', $nit));
    }

    protected function buildItems(Sale $sale): array
    {
        return [[
            'description' => 'Venta combustible '.($sale->fuel?->name ?? 'Combustible'),
            'qty' => (float) $sale->gallons,
            'price' => (float) $sale->price_per_gallon,
            'type' => 'Bien',
        ]];
    }

    protected function normalizeIssuedAt(mixed $issuedAt): Carbon
    {
        if (blank($issuedAt)) {
            return now();
        }

        return Carbon::parse($issuedAt);
    }

    protected function logEvent(
        FelDocument $document,
        string $eventType,
        string $description,
        ?array $payload = null,
        ?array $response = null,
    ): void {
        FelEvent::create([
            'fel_document_id' => $document->id,
            'event_type' => $eventType,
            'description' => $description,
            'payload' => $payload,
            'response' => $response,
        ]);
    }

    public function cancelDocument(FelDocument $document, string $reason): FelDocument
    {
        $document->refresh();

        if ($document->fel_status !== 'certified') {
            throw new \RuntimeException('Solo se pueden anular documentos FEL certificados.');
        }

        if (blank($document->uuid)) {
            throw new \RuntimeException('El documento no tiene UUID FEL para anular.');
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

        $this->logEvent(
            $document,
            'cancel',
            'Solicitud de anulación FEL',
            payload: $payload
        );

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

            $this->logEvent(
                $document->fresh(),
                'success',
                'Documento FEL anulado correctamente',
                payload: $payload,
                response: $responsePayload
            );

            return $document->fresh();
        } catch (Throwable $e) {
            $mapped = $this->errorMapper->map($e);

            $document->update([
                'error_message' => $mapped['message'],
                'response_payload' => array_merge($document->response_payload ?? [], [
                    'cancel_error' => $mapped,
                ]),
            ]);

            $this->logEvent(
                $document->fresh(),
                'error',
                'Error al anular FEL',
                payload: $payload,
                response: ['error' => $mapped]
            );

            throw new \RuntimeException($mapped['message'].' '.$mapped['hint'], previous: $e);
        }
    }
}