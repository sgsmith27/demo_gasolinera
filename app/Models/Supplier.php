<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'nit',
        'phone',
        'email',
        'address',
        'supplier_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function accountPayables()
    {
        return $this->hasMany(\App\Models\AccountPayable::class, 'supplier_id');
    }
}