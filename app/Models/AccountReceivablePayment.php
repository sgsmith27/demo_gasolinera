<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReceivablePayment extends Model
{
    protected $fillable = [
        'account_receivable_id',
        'paid_at',
        'amount_q',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount_q' => 'decimal:2',
    ];

    public function accountReceivable(): BelongsTo
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}