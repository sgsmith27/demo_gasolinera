<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'sold_at',
        'user_id',
        'nozzle_id',
        'fuel_id',
        'price_per_gallon',
        'gallons',
        'total_amount_q',
        'sale_mode',
        'payment_method',
        'notes',
        'status',
        'voided_at',
        'voided_by',
        'void_reason',
        'shift_id',
        'customer_id',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'gallons' => 'decimal:3',
        'price_per_gallon' => 'decimal:4',
        'total_amount_q' => 'decimal:2',
        'voided_at' => 'datetime',        
    ];

    public function nozzle(): BelongsTo
    {
        return $this->belongsTo(Nozzle::class);
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }

    public function shift()
    {
    return $this->belongsTo(\App\Models\WorkShift::class, 'shift_id');
    }

        public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function felDocuments()
    {
        return $this->hasMany(\App\Models\FelDocument::class);
    }

    public function latestFelDocument()
    {
        return $this->hasOne(\App\Models\FelDocument::class)->latestOfMany();
    }


}