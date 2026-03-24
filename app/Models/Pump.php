<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pump extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    public function nozzles(): HasMany
    {
        return $this->hasMany(Nozzle::class);
    }
}