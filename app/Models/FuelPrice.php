<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    protected $fillable = ['fuel_id', 'price_per_gallon', 'valid_from', 'created_by'];

    protected $casts = [
        'valid_from' => 'datetime',
    ];

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }
}