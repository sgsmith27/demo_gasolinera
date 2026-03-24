<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountPayable extends Model
{
    protected $table = 'account_payables';

    protected $fillable = [
        'supplier_id',
        'document_date',
        'document_no',
        'category',
        'description',
        'original_amount_q',
        'paid_amount_q',
        'balance_q',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'original_amount_q' => 'decimal:2',
        'paid_amount_q' => 'decimal:2',
        'balance_q' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountPayablePayment::class, 'account_payable_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}