<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelDelivery extends Model
{
    protected $fillable = [
        'delivered_at',
        'tank_id',
        'fuel_id',
        'gallons',
        'total_cost_q',
        'created_by',
        'notes',
        'status',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'gallons' => 'decimal:3',
        'total_cost_q' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class);
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }
}