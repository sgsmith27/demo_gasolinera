<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nozzle extends Model
{
    protected $fillable = ['pump_id', 'fuel_id', 'code', 'is_active'];

    protected $guarded = [];

    public function pump(): BelongsTo
    {
        return $this->belongsTo(Pump::class);
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class);
    }
}