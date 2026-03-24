<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountReceivable extends Model
{
    protected $fillable = [
        'customer_id',
        'sale_id',
        'document_date',
        'original_amount_q',
        'paid_amount_q',
        'balance_q',
        'status',
        'notes',
    ];

    protected $casts = [
        'document_date' => 'date',
        'original_amount_q' => 'decimal:2',
        'paid_amount_q' => 'decimal:2',
        'balance_q' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountReceivablePayment::class, 'account_receivable_id');
    }
}