<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fuel extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function prices(): HasMany
    {
        return $this->hasMany(FuelPrice::class);
    }

    public function tanks(): HasMany
    {
        return $this->hasMany(Tank::class);
    }
}