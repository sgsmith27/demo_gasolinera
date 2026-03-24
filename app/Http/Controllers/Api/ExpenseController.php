<?php

namespace App\Http\Controllers\Api;

use App\Support\Audit;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\VoidExpenseRequest;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Expense::query();

        if ($from) {
            $query->whereDate('expense_date', '>=', Carbon::parse($from)->toDateString());
        }

        if ($to) {
            $query->whereDate('expense_date', '<=', Carbon::parse($to)->toDateString());
        }

        $expenses = $query
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->get();

        $totalsByCategory = Expense::query()
            ->where('status','active')
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', Carbon::parse($from)->toDateString()))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', Carbon::parse($to)->toDateString()))
            ->groupBy('category')
            ->orderBy('category')
            ->selectRaw('category, COUNT(*) as items_count, COALESCE(SUM(amount_q), 0) as total_q')
            ->get();

        $grandTotal = Expense::query()
            ->where('status','active')
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', Carbon::parse($from)->toDateString()))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', Carbon::parse($to)->toDateString()))
            ->selectRaw('COALESCE(SUM(amount_q), 0) as total_q')
            ->first();

        return response()->json([
            'range' => [
                'from' => $from,
                'to' => $to,
            ],
            'grand_total' => $grandTotal,
            'totals_by_category' => $totalsByCategory,
            'expenses' => $expenses,
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $userId = (int) Auth::id();

        $expense = Expense::create([
            'expense_date' => $request->validated('expense_date'),
            'category' => $request->validated('category'),
            'concept' => $request->validated('concept'),
            'amount_q' => $request->validated('amount_q'),
            'notes' => $request->validated('notes'),
            'created_by' => $userId,
        ]);

        return response()->json([
            'message' => 'Gasto registrado',
            'data' => $expense,
        ], 201);
    }

    public function void(VoidExpenseRequest $request, Expense $expense): JsonResponse
{
    if ($expense->status === 'voided') {
        return response()->json([
            'message' => 'El gasto ya fue anulado'
        ],422);
    }

    $expense->update([
        'status' => 'voided',
        'voided_at' => now(),
        'voided_by' => Auth::id(),
        'void_reason' => $request->validated('reason')
    ]);

    Audit::log(
        module: 'expenses',
        action: 'void',
        entityType: 'Expense',
        entityId: $expense->id,
        description: 'Gasto anulado',
        meta: [
            'reason' => $request->validated('reason'),
            'category' => $expense->category,
            'amount_q' => $expense->amount_q,
            'concept' => $expense->concept,
        ]
    );

    return response()->json([
        'message' => 'Gasto anulado correctamente'
    ]);
}
}