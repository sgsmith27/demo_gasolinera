<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tank extends Model
{
    protected $fillable = ['fuel_id', 'name', 'capacity_gallons', 'current_gallons', 'is_active'];

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}