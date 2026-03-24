<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountPayablePaymentRequest;
use App\Http\Requests\StoreAccountPayableRequest;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;
use App\Models\Supplier;
use App\Support\Audit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountPayableController extends Controller
{
    public function index(): View
    {
        $from = request('from') ?: now()->startOfMonth()->toDateString();
        $to = request('to') ?: now()->endOfMonth()->toDateString();
        $supplierId = request('supplier_id');
        $status = request('status');

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $accounts = AccountPayable::query()
            ->with(['supplier'])
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountPayable::query()
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        return view('accounts-payable.index', compact(
            'accounts',
            'suppliers',
            'from',
            'to',
            'supplierId',
            'status',
            'summary'
        ));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'nit']);

        $today = now()->toDateString();

        return view('accounts-payable.create', compact('suppliers', 'today'));
    }

    public function store(StoreAccountPayableRequest $request): RedirectResponse
    {
        $amount = round((float) $request->validated('original_amount_q'), 2);

        $accountPayable = AccountPayable::create([
            'supplier_id' => $request->validated('supplier_id'),
            'document_date' => $request->validated('document_date'),
            'document_no' => $request->validated('document_no'),
            'category' => $request->validated('category'),
            'description' => $request->validated('description'),
            'original_amount_q' => $amount,
            'paid_amount_q' => 0,
            'balance_q' => $amount,
            'status' => 'pending',
            'notes' => $request->validated('notes'),
            'created_by' => Auth::id(),
        ]);

        Audit::log(
            module: 'accounts_payable',
            action: 'create',
            entityType: 'AccountPayable',
            entityId: $accountPayable->id,
            description: 'Cuenta por pagar creada',
            meta: [
                'supplier_id' => $accountPayable->supplier_id,
                'original_amount_q' => $accountPayable->original_amount_q,
                'category' => $accountPayable->category,
                'document_no' => $accountPayable->document_no,
            ]
        );

        return redirect('/accounts-payable')->with('success', 'Cuenta por pagar creada correctamente.');
    }

    public function show(AccountPayable $accountPayable): View
    {
        $accountPayable->load([
            'supplier',
            'payments.creator',
        ]);

        return view('accounts-payable.show', compact('accountPayable'));
    }

    public function storePayment(StoreAccountPayablePaymentRequest $request, AccountPayable $accountPayable): RedirectResponse
    {
        if ($accountPayable->status === 'paid') {
            return back()->withErrors(['payment' => 'La cuenta ya está pagada.']);
        }

        $amount = round((float) $request->validated('amount_q'), 2);
        $balance = round((float) $accountPayable->balance_q, 2);

        if ($amount > $balance) {
            return back()->withErrors(['amount_q' => 'El pago no puede ser mayor al saldo pendiente.']);
        }

        AccountPayablePayment::create([
            'account_payable_id' => $accountPayable->id,
            'paid_at' => $request->validated('paid_at') ? now()->parse($request->validated('paid_at')) : now(),
            'amount_q' => $amount,
            'payment_method' => $request->validated('payment_method'),
            'notes' => $request->validated('notes'),
            'created_by' => Auth::id(),
        ]);

        $newPaid = round((float) $accountPayable->paid_amount_q + $amount, 2);
        $newBalance = round((float) $accountPayable->original_amount_q - $newPaid, 2);

        $accountPayable->update([
            'paid_amount_q' => $newPaid,
            'balance_q' => $newBalance,
            'status' => $newBalance <= 0 ? 'paid' : 'pending',
        ]);

        Audit::log(
            module: 'accounts_payable',
            action: 'update',
            entityType: 'AccountPayable',
            entityId: $accountPayable->id,
            description: 'Pago registrado a cuenta por pagar',
            meta: [
                'payment_amount_q' => $amount,
                'new_balance_q' => $newBalance,
                'payment_method' => $request->validated('payment_method'),
            ]
        );

        return redirect("/accounts-payable/{$accountPayable->id}")
            ->with('success', 'Pago registrado correctamente.');
    }

    public function pdf()
    {
        $from = request('from') ?: now()->startOfMonth()->toDateString();
        $to = request('to') ?: now()->endOfMonth()->toDateString();
        $supplierId = request('supplier_id');
        $status = request('status');

        $accounts = AccountPayable::query()
            ->with(['supplier'])
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountPayable::query()
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        $supplierLabel = 'Todos';
        if ($supplierId) {
            $supplier = Supplier::query()->find($supplierId);
            $supplierLabel = $supplier?->name ?? 'Desconocido';
        }

        $statusLabel = match ($status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagada',
            default => 'Todos',
        };

        $pdf = Pdf::loadView('pdf.accounts-payable-report', compact(
            'accounts',
            'summary',
            'from',
            'to',
            'supplierLabel',
            'statusLabel'
        ));

        return $pdf->stream('cuentas-por-pagar.pdf');
    }

    public function supplierStatement(Supplier $supplier): View
    {
        $accounts = AccountPayable::query()
            ->with(['payments.creator'])
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountPayable::query()
            ->where('supplier_id', $supplier->id)
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        return view('accounts-payable.statement', compact('supplier', 'accounts', 'summary'));
    }

    public function supplierStatementPdf(Supplier $supplier)
    {
        $accounts = AccountPayable::query()
            ->with(['payments.creator'])
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountPayable::query()
            ->where('supplier_id', $supplier->id)
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        $pdf = Pdf::loadView('pdf.supplier-statement', compact('supplier', 'accounts', 'summary'));

        return $pdf->stream('estado-cuenta-proveedor.pdf');
    }

    public function accountPdf(AccountPayable $accountPayable)
    {
        $accountPayable->load([
            'supplier',
            'payments.creator',
        ]);

        $pdf = Pdf::loadView('pdf.account-payable-detail', compact('accountPayable'));

        return $pdf->stream("cuenta-por-pagar-{$accountPayable->id}.pdf");
    }
}