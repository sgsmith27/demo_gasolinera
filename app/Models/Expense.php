<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_date',
        'category',
        'concept',
        'amount_q',
        'notes',
        'created_by',
        'status',
        'voided_at',
        'voided_by',
        'void_reason'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount_q' => 'decimal:2',
        'voided_at' => 'datetime'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}