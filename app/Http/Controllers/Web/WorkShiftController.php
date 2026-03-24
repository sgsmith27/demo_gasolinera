<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloseWorkShiftRequest;
use App\Http\Requests\StoreWorkShiftRequest;
use App\Models\User;
use App\Models\WorkShift;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class WorkShiftController extends Controller
{
    public function index(): View
    {
        $shifts = WorkShift::query()
            ->with(['user', 'opener', 'closer','pump'])
            ->orderByDesc('started_at')
            ->limit(100)
            ->get();

        return view('work-shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        $users = User::query()
            ->where('role', 'despachador')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pumps = \App\Models\Pump::query()
        ->where('is_active', true)
        ->orderBy('code')
        ->get();    

        $now = now()->format('Y-m-d\TH:i');
        return view('work-shifts.create', compact('users', 'now', 'pumps'));
    }

    public function store(StoreWorkShiftRequest $request): RedirectResponse
    {
        $userId = (int) $request->validated('user_id');

        $openShift = WorkShift::query()
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($openShift) {
            return back()
                ->withErrors(['user_id' => 'El despachador ya tiene un turno abierto.'])
                ->withInput();
        }

        $shift = WorkShift::create([
            'user_id' => $userId,
            'started_at' => $request->validated('started_at') ? now()->parse($request->validated('started_at')) : now(),
            'status' => 'open',
            'opening_cash_q' => $request->validated('opening_cash_q'),
            'opening_notes' => $request->validated('opening_notes'),
            'opened_by' => Auth::id(),
            'assignment_mode' => $request->validated('assignment_mode'),
            'pump_id' => $request->validated('assignment_mode') === 'fixed'
                ? $request->validated('pump_id')
                : null,
        ]);

       Audit::log(
        module: 'work_shifts',
        action: 'create',
        entityType: 'WorkShift',
        entityId: $shift->id,
        description: 'Turno abierto',
        meta: [
            'user_id' => $shift->user_id,
            'started_at' => $shift->started_at,
            'opening_cash_q' => $shift->opening_cash_q,
            'assignment_mode' => $shift->assignment_mode,
            'pump_id' => $shift->pump_id,
        ]
        );

        return redirect('/work-shifts')->with('success', 'Turno abierto correctamente.');
    }

    public function show(WorkShift $workShift): View
    {
        $workShift->load(['user', 'opener', 'closer', 'pump']);

        $salesSummary = $workShift->sales()
            ->where('status', 'active')
            ->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('COALESCE(SUM(total_amount_q), 0) as total_q')
            ->selectRaw('COALESCE(SUM(gallons), 0) as total_gallons')
            ->first();

        $salesByPayment = $workShift->sales()
            ->where('status', 'active')
            ->groupBy('payment_method')
            ->orderBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as total_sales, COALESCE(SUM(total_amount_q),0) as total_q')
            ->get();
        
        $cashSales = $workShift->sales()
            ->where('status', 'active')
            ->where('payment_method', 'cash')
            ->sum('total_amount_q');

$expectedCashPreview = round((float) $workShift->opening_cash_q + (float) $cashSales, 2);    

        $latestSales = $workShift->sales()
            ->where('status', 'active')
            ->with('fuel')
            ->orderByDesc('sold_at')
            ->limit(20)
            ->get();

        return view('work-shifts.show', compact('workShift', 'salesSummary', 'salesByPayment', 'latestSales', 'expectedCashPreview'));
    }

    public function close(CloseWorkShiftRequest $request, WorkShift $workShift): RedirectResponse
    {
        if ($workShift->status === 'closed') {
            return back()->withErrors(['shift' => 'El turno ya está cerrado.']);
        }

        $cashSales = $workShift->sales()
            ->where('status', 'active')
            ->where('payment_method', 'cash')
            ->sum('total_amount_q');

        $expectedCash = round((float) $workShift->opening_cash_q + (float) $cashSales, 2);
        $deliveredCash = round((float) $request->validated('delivered_cash_q'), 2);
        $difference = round($deliveredCash - $expectedCash, 2);

        $workShift->update([
            'status' => 'closed',
            'ended_at' => now(),
            'expected_cash_q' => $expectedCash,
            'delivered_cash_q' => $deliveredCash,
            'cash_difference_q' => $difference,
            'closing_notes' => $request->validated('closing_notes'),
            'closed_by' => Auth::id(),
        ]);

        Audit::log(
            module: 'work_shifts',
            action: 'update',
            entityType: 'WorkShift',
            entityId: $workShift->id,
            description: 'Turno cerrado',
            meta: [
                'user_id' => $workShift->user_id,
                'started_at' => $workShift->started_at,
                'ended_at' => $workShift->ended_at,
                'opening_cash_q' => $workShift->opening_cash_q,
                'expected_cash_q' => $workShift->expected_cash_q,
                'delivered_cash_q' => $workShift->delivered_cash_q,
                'cash_difference_q' => $workShift->cash_difference_q,
            ]
        );

        return redirect("/work-shifts/{$workShift->id}")->with('success', 'Turno cerrado correctamente.');
    }

    public function pdf(WorkShift $workShift)
        {
            $workShift->load(['user', 'opener', 'closer', 'pump']);

            $salesSummary = $workShift->sales()
                ->where('status', 'active')
                ->selectRaw('COUNT(*) as total_sales')
                ->selectRaw('COALESCE(SUM(total_amount_q), 0) as total_q')
                ->selectRaw('COALESCE(SUM(gallons), 0) as total_gallons')
                ->first();

            $salesByPayment = $workShift->sales()
                ->where('status', 'active')
                ->groupBy('payment_method')
                ->orderBy('payment_method')
                ->selectRaw('payment_method, COUNT(*) as total_sales, COALESCE(SUM(total_amount_q),0) as total_q')
                ->get();

            $latestSales = $workShift->sales()
                ->where('status', 'active')
                ->with('fuel')
                ->orderByDesc('sold_at')
                ->get();

            $cashSales = $workShift->sales()
                ->where('status', 'active')
                ->where('payment_method', 'cash')
                ->sum('total_amount_q');

            $expectedCash = $workShift->expected_cash_q ?? round((float) $workShift->opening_cash_q + (float) $cashSales, 2);

            $pdf = Pdf::loadView('pdf.work-shift', compact(
                'workShift',
                'salesSummary',
                'salesByPayment',
                'latestSales',
                'cashSales',
                'expectedCash'
            ));

            return $pdf->stream("turno-{$workShift->id}.pdf");
        }

    public function report(): View
{
    $from = request('from') ?: now()->startOfMonth()->toDateString();
    $to = request('to') ?: now()->endOfMonth()->toDateString();
    $userId = request('user_id');

    $users = \App\Models\User::query()
        ->where('role', 'despachador')
        ->orderBy('name')
        ->get(['id', 'name']);

    $shifts = WorkShift::query()
        ->with(['user', 'pump'])
        ->when($userId, fn ($q) => $q->where('user_id', $userId))
        ->whereBetween('started_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay(),
        ])
        ->orderByDesc('started_at')
        ->get();

    return view('work-shifts.report', compact('shifts', 'from', 'to', 'userId', 'users'));
}

public function reportPdf()
{
    $from = request('from') ?: now()->startOfMonth()->toDateString();
    $to = request('to') ?: now()->endOfMonth()->toDateString();
    $userId = request('user_id');

    $shifts = WorkShift::query()
        ->with(['user', 'pump'])
        ->when($userId, fn ($q) => $q->where('user_id', $userId))
        ->whereBetween('started_at', [
            \Carbon\Carbon::parse($from)->startOfDay(),
            \Carbon\Carbon::parse($to)->endOfDay(),
        ])
        ->orderByDesc('started_at')
        ->get();

    $userLabel = 'Todos';

    if ($userId) {
        $user = \App\Models\User::query()->find($userId);
        $userLabel = $user?->name ?? 'Desconocido';
    }

    $pdf = Pdf::loadView('pdf.work-shifts-report', compact('shifts', 'from', 'to', 'userId', 'userLabel'));

    return $pdf->stream('reporte-turnos.pdf');
}


}
