<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'nit',
        'phone',
        'email',
        'address',
        'customer_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function accountsReceivable(): HasMany
    {
        return $this->hasMany(\App\Models\AccountReceivable::class, 'customer_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class, 'customer_id');
    }
}