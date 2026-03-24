<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dailyClose(Request $request): JsonResponse
    {
        $date = $request->query('date'); // YYYY-MM-DD (opcional)
        $day = $date ? Carbon::parse($date) : now();

        $start = $day->copy()->startOfDay();
        $end   = $day->copy()->endOfDay();

        // Totales generales del día
        $totals = Sale::query()
            ->whereBetween('sold_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(total_amount_q),0) as total_q')
            ->selectRaw('COALESCE(SUM(gallons),0) as total_gallons')
            ->selectRaw('COUNT(*) as total_sales')
            ->first();

        // Por bomba (pump)
        $byPump = DB::table('sales as s')
            ->join('nozzles as n', 'n.id', '=', 's.nozzle_id')
            ->join('pumps as p', 'p.id', '=', 'n.pump_id')
            ->where('s.status', 'active')
            ->whereBetween('s.sold_at', [$start, $end])
            ->groupBy('p.id', 'p.code', 'p.name')
            ->orderBy('p.code')
            ->selectRaw('p.id as pump_id, p.code as pump_code, p.name as pump_name')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
            ->selectRaw('COALESCE(SUM(gallons),0) as total_gallons')
            ->get();

        // Por despachador (user)
        $byUser = DB::table('sales as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('s.status', 'active')
            ->whereBetween('s.sold_at', [$start, $end])
            ->groupBy('u.id', 'u.name', 'u.email')
            ->orderBy('u.name')
            ->selectRaw('u.id as user_id, u.name as user_name, u.email as user_email')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
            ->selectRaw('COALESCE(SUM(s.gallons),0) as total_gallons')
            ->get();

        // Por combustible
        $byFuel = DB::table('sales as s')
            ->join('fuels as f', 'f.id', '=', 's.fuel_id')
            ->where('s.status', 'active')
            ->whereBetween('s.sold_at', [$start, $end])
            ->groupBy('f.id', 'f.name')
            ->orderBy('f.name')
            ->selectRaw('f.id as fuel_id, f.name as fuel_name')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
            ->selectRaw('COALESCE(SUM(s.gallons),0) as total_gallons')
            ->get();

        return response()->json([
            'date' => $day->toDateString(),
            'range' => [
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ],
            'totals' => $totals,
            'by_pump' => $byPump,
            'by_user' => $byUser,
            'by_fuel' => $byFuel,
        ]);
    }

    public function salesSummary(Request $request): JsonResponse
{
    $from = Carbon::parse($request->query('from'))->startOfDay();
    $to   = Carbon::parse($request->query('to'))->endOfDay();

    $totals = Sale::query()
        ->where('status', 'active')
        ->whereBetween('sold_at', [$from, $to])
        ->selectRaw('COALESCE(SUM(total_amount_q),0) as total_q')
        ->selectRaw('COALESCE(SUM(gallons),0) as total_gallons')
        ->selectRaw('COUNT(*) as total_sales')
        ->first();

    $byPump = DB::table('sales as s')
        ->join('nozzles as n', 'n.id', '=', 's.nozzle_id')
        ->join('pumps as p', 'p.id', '=', 'n.pump_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('p.id', 'p.code', 'p.name')
        ->orderBy('p.code')
        ->selectRaw('p.id as pump_id, p.code as pump_code, p.name as pump_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons),0) as total_gallons')
        ->get();

    $byUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name', 'u.email')
        ->orderBy('u.name')
        ->selectRaw('u.id as user_id, u.name as user_name, u.email as user_email')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons),0) as total_gallons')
        ->get();

    $byFuel = DB::table('sales as s')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('f.id', 'f.name')
        ->orderBy('f.name')
        ->selectRaw('f.id as fuel_id, f.name as fuel_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q),0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons),0) as total_gallons')
        ->get();

    return response()->json([
        'range' => [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ],
        'totals' => $totals,
        'by_pump' => $byPump,
        'by_user' => $byUser,
        'by_fuel' => $byFuel,
    ]);
}

public function cashierClose(Request $request): JsonResponse
{
    $date = $request->query('date');
    $day = $date ? Carbon::parse($date) : now();

    $start = $day->copy()->startOfDay();
    $end   = $day->copy()->endOfDay();

    $summaryByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$start, $end])
        ->groupBy('u.id', 'u.name', 'u.email')
        ->orderBy('u.name')
        ->selectRaw('u.id as user_id, u.name as user_name, u.email as user_email')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $paymentsByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$start, $end])
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
        ->whereBetween('s.sold_at', [$start, $end])
        ->groupBy('u.id', 'u.name', 'f.id', 'f.name')
        ->orderBy('u.name')
        ->orderBy('f.name')
        ->selectRaw('u.id as user_id, u.name as user_name')
        ->selectRaw('f.id as fuel_id, f.name as fuel_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    return response()->json([
        'date' => $day->toDateString(),
        'range' => [
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
        ],
        'summary_by_user' => $summaryByUser,
        'payments_by_user' => $paymentsByUser,
        'fuels_by_user' => $fuelsByUser,
    ]);
}

public function cashierCloseRange(Request $request): JsonResponse
{
    $from = Carbon::parse($request->query('from'))->startOfDay();
    $to   = Carbon::parse($request->query('to'))->endOfDay();

    $summaryByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->where('s.status', 'active')
        ->whereBetween('s.sold_at', [$from, $to])
        ->groupBy('u.id', 'u.name', 'u.email')
        ->orderBy('u.name')
        ->selectRaw('u.id as user_id, u.name as user_name, u.email as user_email')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    $paymentsByUser = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->whereBetween('s.sold_at', [$from, $to])
        ->where('s.status', 'active')
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
        ->selectRaw('u.id as user_id, u.name as user_name')
        ->selectRaw('f.id as fuel_id, f.name as fuel_name')
        ->selectRaw('COUNT(*) as sales_count')
        ->selectRaw('COALESCE(SUM(s.total_amount_q), 0) as total_q')
        ->selectRaw('COALESCE(SUM(s.gallons), 0) as total_gallons')
        ->get();

    return response()->json([
        'range' => [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ],
        'summary_by_user' => $summaryByUser,
        'payments_by_user' => $paymentsByUser,
        'fuels_by_user' => $fuelsByUser,
    ]);
}
}