<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'adjusted_at',
        'tank_id',
        'fuel_id',
        'adjustment_type',
        'gallons',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'adjusted_at' => 'datetime',
        'gallons' => 'decimal:3',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class);
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}