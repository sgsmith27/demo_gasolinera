<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelFelDocumentRequest;
use App\Models\FelDocument;
use App\Models\Sale;
use App\Services\Fel\DigifactFelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class FelController extends Controller
{
    public function issueSaleInvoice(Sale $sale, DigifactFelService $service): RedirectResponse
    {
        try {
            $service->issueInvoiceFromSale($sale);

            return back()->with('success', 'Factura FEL emitida correctamente para la venta #' . $sale->id);
        } catch (Throwable $e) {
            return back()->withErrors([
                'fel' => 'Error FEL venta #' . $sale->id . ': ' . $e->getMessage(),
            ]);
        }
    }

    public function show(FelDocument $felDocument): View
    {
        $felDocument->load([
            'sale.fuel',
            'sale.user',
            'customer',
            'creator',
            'events',
        ]);

        return view('fel.show', compact('felDocument'));
    }

    public function downloadPdf(FelDocument $felDocument): Response
    {
        abort_if(empty($felDocument->pdf), 404, 'El documento PDF no está disponible.');

        $content = base64_decode($felDocument->pdf, true);

        if ($content === false) {
            abort(500, 'No fue posible decodificar el PDF.');
        }

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fel-' . $felDocument->id . '.pdf"',
        ]);
    }

    public function downloadXml(FelDocument $felDocument): Response
    {
        abort_if(empty($felDocument->xml), 404, 'El XML no está disponible.');

        $content = base64_decode($felDocument->xml, true);

        if ($content === false) {
            $content = $felDocument->xml;
        }

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fel-' . $felDocument->id . '.xml"',
        ]);
    }

    public function downloadHtml(FelDocument $felDocument): Response
    {
        abort_if(empty($felDocument->html), 404, 'El HTML no está disponible.');

        $content = base64_decode($felDocument->html, true);

        if ($content === false) {
            $content = $felDocument->html;
        }

        return response($content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="fel-' . $felDocument->id . '.html"',
        ]);
    }

    public function cancel(CancelFelDocumentRequest $request, FelDocument $felDocument, DigifactFelService $service): RedirectResponse
    {
        try {
            $service->cancelDocument($felDocument, $request->validated('reason'));

            return back()->with('success', 'Documento FEL anulado correctamente.');
        } catch (Throwable $e) {
            return back()->withErrors([
                'fel' => 'No fue posible anular FEL: ' . $e->getMessage(),
            ]);
        }
    }
}