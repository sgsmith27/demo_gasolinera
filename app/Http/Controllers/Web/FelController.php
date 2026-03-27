<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\Fel\DigifactFelService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class FelController extends Controller
{
    public function issueSaleInvoice(Sale $sale, \App\Services\Fel\DigifactFelService $service): RedirectResponse
{
    try {
        $service->issueInvoiceFromSale($sale);

        return back()->with('success', 'Factura FEL emitida correctamente para la venta #' . $sale->id);
    } catch (\Throwable $e) {
        return back()->withErrors([
            'fel' => 'Error FEL venta #' . $sale->id . ': ' . $e->getMessage(),
        ]);
    }
}
}