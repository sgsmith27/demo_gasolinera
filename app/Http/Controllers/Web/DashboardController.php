<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Expense;
use App\Exports\FelReportExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function home()
    {
        return view('home');
    }

    public function newSale()
{
    $query = \Illuminate\Support\Facades\DB::table('nozzles as n')
        ->join('fuels as f', 'f.id', '=', 'n.fuel_id')
        ->join('pumps as p', 'p.id', '=', 'n.pump_id')
        ->where('n.is_active', true)
        ->where('p.is_active', true);

    if (Auth::check() && Auth::user()->role === 'despachador') {
        $currentShift = \App\Models\WorkShift::query()
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('status', 'open')
            ->latest('started_at')
            ->first();    

        if ($currentShift && $currentShift->assignment_mode === 'fixed' && $currentShift->pump_id) {
            $query->where('n.pump_id', $currentShift->pump_id);
        }
    }

    $shiftInfo = null;
        if (Auth::check() && Auth::user()->role === 'despachador') {
            $currentShift = \App\Models\WorkShift::query()
                ->with('pump')
                ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->where('status', 'open')
                ->latest('started_at')
                ->first();

            if ($currentShift) {
                $shiftInfo = $currentShift;
            }

            if ($currentShift && $currentShift->assignment_mode === 'fixed' && $currentShift->pump_id) {
                $query->where('n.pump_id', $currentShift->pump_id);
            }
        }

    $nozzles = $query
        ->orderBy('n.code')
        ->select(
            'n.id',
            'n.code',
            'n.fuel_id',
            'n.pump_id',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name'),
            \Illuminate\Support\Facades\DB::raw('p.code as pump_code')
        )
        ->get();

    $customers = \App\Models\Customer::query()
    ->where('is_active', true)
    ->whereIn('customer_type', ['credit', 'mixed'])
    ->orderBy('name')
    ->get(['id', 'name', 'nit']);    

    return view('sales.new', compact('nozzles', 'customers', 'shiftInfo'));
}

    public function dailyClose()
    {
        $today = now()->toDateString();
        return view('reports.daily-close', compact('today'));
    }

    public function inventory()
{
    $tanks = \Illuminate\Support\Facades\DB::table('tanks as t')
        ->join('fuels as f', 'f.id', '=', 't.fuel_id')
        ->orderBy('t.id')
        ->select(
            't.id',
            't.fuel_id',
            't.name',
            't.capacity_gallons',
            't.current_gallons',
            't.is_active',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name')
        )
        ->get();

    $movements = \Illuminate\Support\Facades\DB::table('inventory_movements as m')
        ->join('fuels as f', 'f.id', '=', 'm.fuel_id')
        ->join('tanks as t', 't.id', '=', 'm.tank_id')
        ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
        ->orderByDesc('m.moved_at')
        ->limit(30)
        ->select(
            'm.moved_at',
            'm.movement_type',
            'm.gallons_delta',
            'm.reference_type',
            'm.reference_id',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name'),
            \Illuminate\Support\Facades\DB::raw('t.id as tank_id'),
            \Illuminate\Support\Facades\DB::raw("COALESCE(u.name, '-') as created_by_name")
        )
        ->get();

    return view('inventory.index', compact('tanks', 'movements'));
}

public function cashierClose()
{
    $today = now()->toDateString();
    return view('reports.cashier-close', compact('today'));
}

public function salesSummary()
{
    $today = now()->toDateString();
    return view('reports.sales-summary', compact('today'));
}

public function cashierCloseRange()
{
    $today = now()->toDateString();
    return view('reports.cashier-close-range', compact('today'));
}

public function salesSummaryPdf()
{
    $from = Carbon::parse(request('from'))->startOfDay();
    $to   = Carbon::parse(request('to'))->endOfDay();

    $totals = Sale::query()
        ->whereBetween('sold_at', [$from, $to])
        ->selectRaw('COALESCE(SUM(total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(gallons), 0) as total_gallons')
        ->selectRaw('COUNT(*) as total_sales')
        ->first();

    $byPump = DB::table('sales as s')
        ->join('nozzles as n', 'n.id', '=', 's.nozzle_id')
        ->join('pumps as p', 'p.id', '=', 'n.pump_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('p.id', 'p.code', 'p.name')
        ->orderBy('p.code')
        ->selectRaw('p.code as pump_code')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $byUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name')
        ->orderBy('u.name')
        ->selectRaw('u.name as user_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $byFuel = DB::table('sales as s')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('f.id', 'f.name')
        ->orderBy('f.name')
        ->selectRaw('f.name as fuel_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $pdf = Pdf::loadView('pdf.sales-summary', compact('from', 'to', 'totals', 'byPump', 'byUser', 'byFuel'));
    return $pdf->stream('sales-summary.pdf');
}

public function cashierCloseRangePdf()
{
    $from = Carbon::parse(request('from'))->startOfDay();
    $to   = Carbon::parse(request('to'))->endOfDay();

    $summaryByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name')
        ->orderBy('u.name')
        ->selectRaw('u.id as user_id, u.name as user_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $paymentsByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name', 's.payment_method')
        ->orderBy('u.name')
        ->selectRaw('u.id as user_id, u.name as user_name, s.payment_method')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $fuelsByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name', 'f.id', 'f.name')
        ->orderBy('u.name')
        ->orderBy('f.name')
        ->selectRaw('u.id as user_id, u.name as user_name, f.name as fuel_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $pdf = Pdf::loadView('pdf.cashier-close-range', compact('from', 'to', 'summaryByUser', 'paymentsByUser', 'fuelsByUser'));
    return $pdf->stream('cashier-close-range.pdf');
}

public function expenses()
{
    $today = now()->toDateString();
    return view('expenses.index', compact('today'));
}

public function newExpense()
{
    $today = now()->toDateString();
    $categories = ['Sueldos', 'Alquiler', 'Servicios', 'Mantenimiento', 'Papeleria', 'Otros'];

    return view('expenses.new', compact('today', 'categories'));
}

public function expensesPdf()
{
    $from = request('from') ? Carbon::parse(request('from'))->toDateString() : null;
    $to = request('to') ? Carbon::parse(request('to'))->toDateString() : null;

    $expensesQuery = Expense::query();

    if ($from) {
        $expensesQuery->whereDate('expense_date', '>=', $from);
    }

    if ($to) {
        $expensesQuery->whereDate('expense_date', '<=', $to);
    }

    $expenses = $expensesQuery
        ->where('status', 'active')
        ->orderByDesc('expense_date')
        ->orderByDesc('id')
        ->get();

    $totalsByCategory = Expense::query()
        ->where('status','active')
        ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
        ->groupBy('category')
        ->orderBy('category')
        ->selectRaw('category, COUNT(*) as items_count, COALESCE(SUM(amount_q), 0) as total_q')
        ->get();

    $grandTotal = Expense::query()
        ->where('status','active')
        ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
        ->selectRaw('COALESCE(SUM(amount_q), 0) as total_q')
        ->first();

    $pdf = Pdf::loadView('pdf.expenses', compact('from', 'to', 'expenses', 'totalsByCategory', 'grandTotal'));

    return $pdf->stream('expenses-report.pdf');
}

public function fuelPrices()
{
    $fuels = \App\Models\Fuel::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('fuel-prices.index', compact('fuels'));
}

public function newFuelPrice()
{
    $fuels = \App\Models\Fuel::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $now = now()->format('Y-m-d\TH:i');

    return view('fuel-prices.new', compact('fuels', 'now'));
}

public function newFuelDelivery()
{
    $today = now()->format('Y-m-d\TH:i');

    $tanks = \Illuminate\Support\Facades\DB::table('tanks as t')
        ->join('fuels as f', 'f.id', '=', 't.fuel_id')
        ->where('t.is_active', true)
        ->orderBy('t.id')
        ->select(
            't.id',
            't.name',
            't.current_gallons',
            't.capacity_gallons',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name')
        )
        ->get();

    return view('fuel-deliveries.new', compact('today', 'tanks'));
}

public function reports()
{
    return view('reports.index');
}

public function salesIndex()
{
    $today = now()->toDateString();
    return view('sales.index', compact('today'));
}

public function fuelDeliveriesIndex()
{
    $today = now()->toDateString();
    return view('fuel-deliveries.index', compact('today'));
}

public function fuelDeliveriesPdf()
{
    $from = request('from') ? Carbon::parse(request('from'))->toDateString() : null;
    $to = request('to') ? Carbon::parse(request('to'))->toDateString() : null;

    $deliveries = DB::table('fuel_deliveries as d')
        ->join('fuels as f', 'f.id', '=', 'd.fuel_id')
        ->join('tanks as t', 't.id', '=', 'd.tank_id')
        ->leftJoin('users as u', 'u.id', '=', 'd.created_by')
        ->when($from, fn ($q) => $q->whereDate('d.delivered_at', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('d.delivered_at', '<=', $to))
        ->orderByDesc('d.delivered_at')
        ->selectRaw('d.id, d.delivered_at, d.gallons, d.total_cost_q, d.status, f.name as fuel_name, t.id as tank_id, COALESCE(u.name, \'-\') as user_name')
        ->get();

    $totals = DB::table('fuel_deliveries as d')
        ->when($from, fn ($q) => $q->whereDate('d.delivered_at', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('d.delivered_at', '<=', $to))
        ->where('d.status', 'active')
        ->selectRaw('COUNT(*) as total_items, COALESCE(SUM(d.gallons), 0) as total_gallons, COALESCE(SUM(d.total_cost_q), 0) as total_cost_q')
        ->first();

    $pdf = Pdf::loadView('pdf.fuel-deliveries', compact('from', 'to', 'deliveries', 'totals'));

    return $pdf->stream('fuel-deliveries-report.pdf');
}

public function newInventoryAdjustment()
{
    $now = now()->format('Y-m-d\TH:i');

    $tanks = \Illuminate\Support\Facades\DB::table('tanks as t')
        ->join('fuels as f', 'f.id', '=', 't.fuel_id')
        ->where('t.is_active', true)
        ->orderBy('t.id')
        ->select(
            't.id',
            't.name',
            't.current_gallons',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name')
        )
        ->get();

    return view('inventory-adjustments.new', compact('now', 'tanks'));
}

public function dashboard()
{
    $todayStart = now()->startOfDay();
    $todayEnd = now()->endOfDay();

    $monthStart = now()->startOfMonth();
    $monthEnd = now()->endOfMonth();

    $salesToday = \Illuminate\Support\Facades\DB::table('sales')
        ->where('status', 'active')
        ->whereBetween('sold_at', [$todayStart, $todayEnd])
        ->selectRaw('COUNT(*) as total_sales, COALESCE(SUM(total_amount_q), 0) as total_q, COALESCE(SUM(gallons), 0) as total_gallons')
        ->first();

    $expensesMonth = \Illuminate\Support\Facades\DB::table('expenses')
        ->where('status', 'active')
        ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
        ->selectRaw('COALESCE(SUM(amount_q), 0) as total_q')
        ->first();

    $inventory = \Illuminate\Support\Facades\DB::table('tanks as t')
        ->join('fuels as f', 'f.id', '=', 't.fuel_id')
        ->where('t.is_active', true)
        ->orderBy('f.name')
        ->select(
            't.id',
            't.current_gallons',
            't.capacity_gallons',
            \Illuminate\Support\Facades\DB::raw('f.name as fuel_name')
        )
        ->get();

    $alerts = $inventory->filter(function ($tank) {
        return (float) $tank->current_gallons <= 100;
    })->values();

    $latestSales = \Illuminate\Support\Facades\DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->where('s.status', 'active')
        ->orderByDesc('s.sold_at')
        ->limit(10)
        ->selectRaw('s.id, s.sold_at, s.gallons, s.total_amount_q, u.name as user_name, f.name as fuel_name')
        ->get();

    $salesByFuelToday = \Illuminate\Support\Facades\DB::table('sales as s')
    ->join('fuels as f', 'f.id', '=', 's.fuel_id')
    ->where('s.status', 'active')
    ->whereBetween('s.sold_at', [$todayStart, $todayEnd])
    ->groupBy('f.name')
    ->orderBy('f.name')
    ->selectRaw('f.name as fuel_name, COALESCE(SUM(s.total_amount_q), 0) as total_q, COALESCE(SUM(s.gallons), 0) as total_gallons')
    ->get();
    
    $last7DaysSales = collect(range(6, 0))->map(function ($daysAgo) {
    $date = now()->subDays($daysAgo)->toDateString();

    $row = \Illuminate\Support\Facades\DB::table('sales')
        ->where('status', 'active')
        ->whereDate('sold_at', $date)
        ->selectRaw('COALESCE(SUM(total_amount_q), 0) as total_q, COALESCE(SUM(gallons), 0) as total_gallons')
        ->first();

    return [
        'date' => $date,
        'total_q' => (float) ($row->total_q ?? 0),
        'total_gallons' => (float) ($row->total_gallons ?? 0),
    ];
});

$salesByFuelLabels = $salesByFuelToday->pluck('fuel_name')->values();
$salesByFuelTotals = $salesByFuelToday->pluck('total_q')->map(function ($v) {
    return (float) $v;
})->values();

$sales7DaysLabels = $last7DaysSales->pluck('date')->values();
$sales7DaysTotals = $last7DaysSales->pluck('total_q')->map(function ($v) {
    return (float) $v;
})->values();

$inventoryChartLabels = $inventory->map(function ($tank) {
    return $tank->fuel_name . ' T#' . $tank->id;
})->values();

$inventoryChartValues = $inventory->map(function ($tank) {
    return (float) $tank->current_gallons;
})->values();

    return view('dashboard-professional', compact(
        'salesToday',
        'expensesMonth',
        'inventory',
        'alerts',
        'latestSales',
        'salesByFuelLabels',
        'salesByFuelTotals',
        'sales7DaysLabels',
        'sales7DaysTotals',
        'inventoryChartLabels',
        'inventoryChartValues',
    ));
}
public function cashierDashboard()
{
    $userId = Auth::id();
    $todayStart = now()->startOfDay();
    $todayEnd = now()->endOfDay();

    $salesToday = \Illuminate\Support\Facades\DB::table('sales')
        ->where('status', 'active')
        ->where('user_id', $userId)
        ->whereBetween('sold_at', [$todayStart, $todayEnd])
        ->selectRaw('COUNT(*) as total_sales, COALESCE(SUM(total_amount_q), 0) as total_q, COALESCE(SUM(gallons), 0) as total_gallons')
        ->first();

    $latestSales = \Illuminate\Support\Facades\DB::table('sales as s')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->where('s.status', 'active')
        ->where('s.user_id', $userId)
        ->orderByDesc('s.sold_at')
        ->limit(10)
        ->selectRaw('s.id, s.sold_at, s.gallons, s.total_amount_q, f.name as fuel_name')
        ->get();

    $currentShift = \App\Models\WorkShift::query()
    ->with('pump')
    ->where('user_id', $userId)
    ->where('status', 'open')
    ->latest('started_at')
    ->first();

    $shiftSalesSummary = null;

    if ($currentShift) {
        $shiftSalesSummary = $currentShift->sales()
            ->where('status', 'active')
            ->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('COALESCE(SUM(total_amount_q), 0) as total_q')
            ->selectRaw('COALESCE(SUM(gallons), 0) as total_gallons')
            ->first();
    }

    return view('dashboard-cashier', compact('salesToday', 'latestSales', 'currentShift', 'shiftSalesSummary'));
}

public function financialSummary()
{
    $from = request('from') ?: now()->startOfMonth()->toDateString();
    $to = request('to') ?: now()->endOfMonth()->toDateString();

    $sales = DB::table('sales')
        ->where('status', 'active')
        ->whereBetween('sold_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_sales')
        ->selectRaw('COALESCE(SUM(total_amount_q),0) as total_q')
        ->first();

    $salesByPayment = DB::table('sales')
        ->where('status', 'active')
        ->whereBetween('sold_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->groupBy('payment_method')
        ->orderBy('payment_method')
        ->selectRaw('payment_method, COUNT(*) as total_sales, COALESCE(SUM(total_amount_q),0) as total_q')
        ->get();

    $expenses = DB::table('expenses')
        ->where('status', 'active')
        ->whereBetween('expense_date', [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();
    
    $fuelDeliveries = DB::table('fuel_deliveries')
    ->whereBetween('delivered_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay(),
    ])
    ->selectRaw('COUNT(*) as total_items')
    ->selectRaw('COALESCE(SUM(total_cost_q),0) as total_q')
    ->first();

    $receivableCollections = DB::table('account_receivable_payments')
        ->whereBetween('paid_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();

    $payablePayments = DB::table('account_payable_payments')
        ->whereBetween('paid_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();

    $pendingReceivables = DB::table('account_receivables')
        ->where('status', 'pending')
        ->selectRaw('COALESCE(SUM(balance_q),0) as total_q')
        ->first();

    $pendingPayables = DB::table('account_payables')
        ->where('status', 'pending')
        ->selectRaw('COALESCE(SUM(balance_q),0) as total_q')
        ->first();

    $salesTotal = (float) ($sales->total_q ?? 0);
    $expensesTotal = (float) ($expenses->total_q ?? 0);
    $fuelDeliveriesTotal = (float) ($fuelDeliveries->total_q ?? 0);
    $receivableCollectionsTotal = (float) ($receivableCollections->total_q ?? 0);
    $payablePaymentsTotal = (float) ($payablePayments->total_q ?? 0);

    $finalBalance = round(
    $salesTotal
    + $receivableCollectionsTotal
    - $fuelDeliveriesTotal
    - $expensesTotal
    - $payablePaymentsTotal,
    2
    ) ;
    $grossMargin = round(
    $salesTotal - $fuelDeliveriesTotal,
    2
    );

    return view('reports.financial-summary', compact(
        'from',
        'to',
        'sales',
        'salesByPayment',
        'expenses',
        'receivableCollections',
        'payablePayments',
        'pendingReceivables',
        'pendingPayables',
        'finalBalance',
        'fuelDeliveries',
        'grossMargin'
    ));
}

public function financialSummaryPdf()
{
    $from = request('from') ?: now()->startOfMonth()->toDateString();
    $to = request('to') ?: now()->endOfMonth()->toDateString();

    $sales = DB::table('sales')
        ->where('status', 'active')
        ->whereBetween('sold_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_sales')
        ->selectRaw('COALESCE(SUM(total_amount_q),0) as total_q')
        ->first();

    $salesByPayment = DB::table('sales')
        ->where('status', 'active')
        ->whereBetween('sold_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->groupBy('payment_method')
        ->orderBy('payment_method')
        ->selectRaw('payment_method, COUNT(*) as total_sales, COALESCE(SUM(total_amount_q),0) as total_q')
        ->get();

    $fuelDeliveries = DB::table('fuel_deliveries')
    ->whereBetween('delivered_at', [
        Carbon::parse($from)->startOfDay(),
        Carbon::parse($to)->endOfDay(),
    ])
    ->selectRaw('COUNT(*) as total_items')
    ->selectRaw('COALESCE(SUM(total_cost_q),0) as total_q')
    ->first();

    $expenses = DB::table('expenses')
        ->where('status', 'active')
        ->whereBetween('expense_date', [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();

    $receivableCollections = DB::table('account_receivable_payments')
        ->whereBetween('paid_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();

    $payablePayments = DB::table('account_payable_payments')
        ->whereBetween('paid_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay(),
        ])
        ->selectRaw('COUNT(*) as total_items')
        ->selectRaw('COALESCE(SUM(amount_q),0) as total_q')
        ->first();

    $pendingReceivables = DB::table('account_receivables')
        ->where('status', 'pending')
        ->selectRaw('COALESCE(SUM(balance_q),0) as total_q')
        ->first();

    $pendingPayables = DB::table('account_payables')
        ->where('status', 'pending')
        ->selectRaw('COALESCE(SUM(balance_q),0) as total_q')
        ->first();

    $salesTotal = (float) ($sales->total_q ?? 0);
    $fuelDeliveriesTotal = (float) ($fuelDeliveries->total_q ?? 0);
    $expensesTotal = (float) ($expenses->total_q ?? 0);
    $receivableCollectionsTotal = (float) ($receivableCollections->total_q ?? 0);
    $payablePaymentsTotal = (float) ($payablePayments->total_q ?? 0);

    $finalBalance = round(
        $salesTotal  + $receivableCollectionsTotal -  $fuelDeliveriesTotal - $expensesTotal - $payablePaymentsTotal,
        2
    );

    $grossMargin = round(
    $salesTotal - $fuelDeliveriesTotal,
    2
    );

    $pdf = Pdf::loadView('pdf.financial-summary', compact(
        'from',
        'to',
        'sales',
        'salesByPayment',
        'expenses',
        'receivableCollections',
        'payablePayments',
        'pendingReceivables',
        'pendingPayables',
        'finalBalance',
        'fuelDeliveries',
        'grossMargin'
    ));

    return $pdf->stream('balance-operativo.pdf');
}

public function fel(Request $request)
{
    $from = $request->input('from', now()->toDateString());
    $to = $request->input('to', now()->toDateString());
    $status = $request->input('status');

    $query = \App\Models\FelDocument::query()
        ->with(['sale.user'])
        ->whereBetween('issued_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

    if ($status) {
        $query->where('fel_status', $status);
    }

    $documents = $query
        ->orderByDesc('issued_at')
        ->get();

    $validDocs = $documents->where('fel_status', 'certified');

$totals = [
    'count' => $documents->count(),
    'certified_count' => $documents->where('fel_status', 'certified')->count(),
    'cancelled_count' => $documents->where('fel_status', 'cancelled')->count(),
    'error_count' => $documents->where('fel_status', 'error')->count(),

    'total_amount' => $validDocs->sum(function ($doc) {
        return (float) ($doc->total_amount_q ?? $doc->sale->total_amount_q ?? 0);
    }),

    'taxable_base' => $validDocs->sum(function ($doc) {
        return (float) ($doc->taxable_base_q ?? 0);
    }),

    'reported_vat_amount' => $validDocs->sum(function ($doc) {
        return (float) ($doc->vat_amount_q ?? 0);
    }),

    'reported_idp_amount' => $validDocs->sum(function ($doc) {
        return (float) ($doc->idp_amount_q ?? 0);
    }),
];

    return view('reports.fel', [
        'documents' => $documents,
        'from' => $from,
        'to' => $to,
        'status' => $status,
        'totals' => $totals,
    ]);
}

public function felPdf(Request $request)
{
    $from = $request->input('from', now()->toDateString());
    $to = $request->input('to', now()->toDateString());
    $status = $request->input('status');

    $query = \App\Models\FelDocument::query()
        ->with(['sale.user'])
        ->whereBetween('issued_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

    if ($status) {
        $query->where('fel_status', $status);
    }

    $documents = $query
        ->orderByDesc('issued_at')
        ->get();

       $validDocs = $documents->where('fel_status', 'certified');

    $totals = [
        'count' => $documents->count(),
        'certified_count' => $documents->where('fel_status', 'certified')->count(),
        'cancelled_count' => $documents->where('fel_status', 'cancelled')->count(),
        'error_count' => $documents->where('fel_status', 'error')->count(),

        'total_amount' => $validDocs->sum(function ($doc) {
            return (float) ($doc->total_amount_q ?? $doc->sale->total_amount_q ?? 0);
        }),

        'taxable_base' => $validDocs->sum(function ($doc) {
            return (float) ($doc->taxable_base_q ?? 0);
        }),

        'reported_vat_amount' => $validDocs->sum(function ($doc) {
            return (float) ($doc->vat_amount_q ?? 0);
        }),

        'reported_idp_amount' => $validDocs->sum(function ($doc) {
            return (float) ($doc->idp_amount_q ?? 0);
        }),
    ];

    $pdf = Pdf::loadView('pdf.fel_pdf', [
        'documents' => $documents,
        'from' => $from,
        'to' => $to,
        'status' => $status,
        'totals' => $totals,
    ])->setPaper('a4', 'landscape');

    return $pdf->download('reporte_fel_' . $from . '_a_' . $to . '.pdf');
}

public function felExcel(Request $request)
{
    $from = $request->input('from', now()->toDateString());
    $to = $request->input('to', now()->toDateString());
    $status = $request->input('status');

    return Excel::download(
        new FelReportExport($from, $to, $status),
        'reporte_fel_' . $from . '_a_' . $to . '.xlsx'
    );
}

}