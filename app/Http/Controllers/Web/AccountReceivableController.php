<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountReceivablePaymentRequest;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\Customer;
use App\Support\Audit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountReceivableController extends Controller
{
    public function index(): View
    {
        $from = request('from') ?: now()->startOfMonth()->toDateString();
        $to = request('to') ?: now()->endOfMonth()->toDateString();
        $customerId = request('customer_id');
        $status = request('status');

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $accounts = AccountReceivable::query()
            ->with(['customer', 'sale'])
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountReceivable::query()
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
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

        return view('accounts-receivable.index', compact(
            'accounts',
            'customers',
            'from',
            'to',
            'customerId',
            'status',
            'summary'
        ));
    }

    public function show(AccountReceivable $accountReceivable): View
    {
        $accountReceivable->load([
            'customer',
            'sale',
            'payments.creator',
        ]);

        return view('accounts-receivable.show', compact('accountReceivable'));
    }

    public function storePayment(StoreAccountReceivablePaymentRequest $request, AccountReceivable $accountReceivable): RedirectResponse
    {
        if ($accountReceivable->status === 'paid') {
            return back()->withErrors(['payment' => 'La cuenta ya está pagada.']);
        }

        $amount = round((float) $request->validated('amount_q'), 2);
        $balance = round((float) $accountReceivable->balance_q, 2);

        if ($amount > $balance) {
            return back()->withErrors(['amount_q' => 'El abono no puede ser mayor al saldo pendiente.']);
        }

        AccountReceivablePayment::create([
            'account_receivable_id' => $accountReceivable->id,
            'paid_at' => $request->validated('paid_at') ? now()->parse($request->validated('paid_at')) : now(),
            'amount_q' => $amount,
            'payment_method' => $request->validated('payment_method'),
            'notes' => $request->validated('notes'),
            'created_by' => Auth::id(),
        ]);

        $newPaid = round((float) $accountReceivable->paid_amount_q + $amount, 2);
        $newBalance = round((float) $accountReceivable->original_amount_q - $newPaid, 2);

        $accountReceivable->update([
            'paid_amount_q' => $newPaid,
            'balance_q' => $newBalance,
            'status' => $newBalance <= 0 ? 'paid' : 'pending',
        ]);

        Audit::log(
            module: 'accounts_receivable',
            action: 'update',
            entityType: 'AccountReceivable',
            entityId: $accountReceivable->id,
            description: 'Abono registrado a cuenta por cobrar',
            meta: [
                'payment_amount_q' => $amount,
                'new_balance_q' => $newBalance,
                'payment_method' => $request->validated('payment_method'),
            ]
        );

        return redirect("/accounts-receivable/{$accountReceivable->id}")
            ->with('success', 'Abono registrado correctamente.');
    }

    public function pdf()
    {
        $from = request('from') ?: now()->startOfMonth()->toDateString();
        $to = request('to') ?: now()->endOfMonth()->toDateString();
        $customerId = request('customer_id');
        $status = request('status');

        $accounts = AccountReceivable::query()
            ->with(['customer', 'sale'])
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->whereBetween('document_date', [
                Carbon::parse($from)->toDateString(),
                Carbon::parse($to)->toDateString(),
            ])
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountReceivable::query()
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
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

        $customerLabel = 'Todos';
        if ($customerId) {
            $customer = Customer::query()->find($customerId);
            $customerLabel = $customer?->name ?? 'Desconocido';
        }

        $statusLabel = match ($status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagada',
            default => 'Todos',
        };

        $pdf = Pdf::loadView('pdf.accounts-receivable-report', compact(
            'accounts',
            'summary',
            'from',
            'to',
            'customerLabel',
            'statusLabel'
        ));

        return $pdf->stream('cuentas-por-cobrar.pdf');
    }

    public function customerStatement(Customer $customer): View
    {
        $accounts = AccountReceivable::query()
            ->with(['sale', 'payments.creator'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountReceivable::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        return view('accounts-receivable.statement', compact('customer', 'accounts', 'summary'));
    }

    public function customerStatementPdf(Customer $customer)
    {
        $accounts = AccountReceivable::query()
            ->with(['sale', 'payments.creator'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('document_date')
            ->get();

        $summary = AccountReceivable::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(original_amount_q),0) as original_total_q')
            ->selectRaw('COALESCE(SUM(paid_amount_q),0) as paid_total_q')
            ->selectRaw('COALESCE(SUM(balance_q),0) as balance_total_q')
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        $pdf = Pdf::loadView('pdf.customer-statement', compact('customer', 'accounts', 'summary'));

        return $pdf->stream('estado-cuenta-cliente.pdf');
    }

    public function accountPdf(AccountReceivable $accountReceivable)
    {
        $accountReceivable->load([
            'customer',
            'sale',
            'payments.creator',
        ]);

        $pdf = Pdf::loadView('pdf.account-receivable-detail', compact('accountReceivable'));

        return $pdf->stream("cuenta-por-cobrar-{$accountReceivable->id}.pdf");
    }
}