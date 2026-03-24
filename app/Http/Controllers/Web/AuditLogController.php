<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->toDateString()
            : now()->startOfMonth()->toDateString();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->toDateString()
            : now()->endOfMonth()->toDateString();

        $module = $request->query('module');
        $userId = $request->query('user_id');
        $action = $request->query('action');

        $modules = [
            'sales' => 'Ventas',
            'fuel_deliveries' => 'Abastecimientos',
            'expenses' => 'Gastos',
            'inventory' => 'Inventario',
            'fuel_prices' => 'Precios',
            'users' => 'Usuarios',
            'pumps' => 'Bombas',
            'nozzles' => 'Mangueras',
        ];

        $actions = [
            'void' => 'Anulación',
            'adjust' => 'Ajuste',
            'create' => 'Creación',
            'update' => 'Actualización',
        ];



        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $logs = AuditLog::query()
            ->with('user')
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('event_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ])
            ->orderByDesc('event_at')
            ->get();

        return view('audit-logs.index', compact(
            'logs',
            'from',
            'to',
            'module',
            'modules',
            'userId',
            'users',
            'action',
            'actions'
        ));
    }

    public function pdf(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->toDateString()
            : now()->startOfMonth()->toDateString();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->toDateString()
            : now()->endOfMonth()->toDateString();

        $module = $request->query('module');
        $userId = $request->query('user_id');
        $action = $request->query('action');

        $modules = [
            'sales' => 'Ventas',
            'fuel_deliveries' => 'Abastecimientos',
            'expenses' => 'Gastos',
            'inventory' => 'Inventario',
            'fuel_prices' => 'Precios',
            'users' => 'Usuarios',
            'pumps' => 'Bombas',
            'nozzles' => 'Mangueras',
        ];

        $actions = [
            'void' => 'Anulación',
            'adjust' => 'Ajuste',
            'create' => 'Creación',
            'update' => 'Actualización',
        ];



        $logs = AuditLog::query()
            ->with('user')
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($module, fn ($q) => $q->where('module', $module))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('event_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ])
            ->orderByDesc('event_at')
            ->get();

        $moduleLabel = $module && isset($modules[$module]) ? $modules[$module] : 'Todos';
        $userLabel = null;

        if ($userId) {
            $user = User::query()->find($userId);
            $userLabel = $user?->name ?? 'Desconocido';
        }

        $actionLabel = $action && isset($actions[$action]) ? $actions[$action] : 'Todas';

        $pdf = Pdf::loadView('pdf.audit-logs', compact(
            'logs',
            'from',
            'to',
            'module',
            'moduleLabel',
            'userId',
            'userLabel',
            'action',
            'actionLabel'
        ));

        return $pdf->stream('bitacora-anulaciones.pdf');
    }

    public function dashboard(Request $request): View
{
    $from = $request->query('from')
        ? Carbon::parse($request->query('from'))->toDateString()
        : now()->startOfMonth()->toDateString();

    $to = $request->query('to')
        ? Carbon::parse($request->query('to'))->toDateString()
        : now()->endOfMonth()->toDateString();

    $baseQuery = AuditLog::query()
        ->whereBetween('event_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay()
        ]);

    $totals = (clone $baseQuery)
        ->selectRaw('COUNT(*) as total_events')
        ->selectRaw("SUM(CASE WHEN action = 'void' THEN 1 ELSE 0 END) as total_void")
        ->selectRaw("SUM(CASE WHEN action = 'create' THEN 1 ELSE 0 END) as total_create")
        ->selectRaw("SUM(CASE WHEN action = 'update' THEN 1 ELSE 0 END) as total_update")
        ->selectRaw("SUM(CASE WHEN action = 'adjust' THEN 1 ELSE 0 END) as total_adjust")
        ->first();

    $byModule = (clone $baseQuery)
        ->selectRaw('module, COUNT(*) as total')
        ->groupBy('module')
        ->orderBy('module')
        ->get();

    $byAction = (clone $baseQuery)
        ->selectRaw('action, COUNT(*) as total')
        ->groupBy('action')
        ->orderBy('action')
        ->get();

    $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
        $date = now()->subDays($daysAgo)->toDateString();

        $row = AuditLog::query()
            ->whereDate('event_at', $date)
            ->selectRaw('COUNT(*) as total')
            ->first();

        return [
            'date' => $date,
            'total' => (int) ($row->total ?? 0),
        ];
    });

    $latestLogs = (clone $baseQuery)
        ->with('user')
        ->orderByDesc('event_at')
        ->limit(20)
        ->get();

    $moduleLabels = $byModule->pluck('module')->map(function ($m) {
        return match ($m) {
            'sales' => 'Ventas',
            'fuel_deliveries' => 'Abastecimientos',
            'expenses' => 'Gastos',
            'inventory' => 'Inventario',
            'fuel_prices' => 'Precios',
            'users' => 'Usuarios',
            'pumps' => 'Bombas',
            'nozzles' => 'Mangueras',
            default => $m,
        };
    })->values();

    $moduleValues = $byModule->pluck('total')->map(fn ($v) => (int) $v)->values();

    $actionLabels = $byAction->pluck('action')->map(function ($a) {
        return match ($a) {
            'void' => 'Anulación',
            'create' => 'Creación',
            'update' => 'Actualización',
            'adjust' => 'Ajuste',
            default => $a,
        };
    })->values();

    $actionValues = $byAction->pluck('total')->map(fn ($v) => (int) $v)->values();

    $daysLabels = $last7Days->pluck('date')->values();
    $daysValues = $last7Days->pluck('total')->map(fn ($v) => (int) $v)->values();

    return view('audit-logs.dashboard', compact(
        'from',
        'to',
        'totals',
        'latestLogs',
        'moduleLabels',
        'moduleValues',
        'actionLabels',
        'actionValues',
        'daysLabels',
        'daysValues'
    ));
}

}
