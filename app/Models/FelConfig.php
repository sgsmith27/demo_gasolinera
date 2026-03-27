<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FelConfig extends Model
{
    protected $table = 'fel_configs';

    protected $fillable = [
        'environment',
        'taxid',
        'username',
        'password',
        'seller_name',
        'seller_address',
        'afiliacion_iva',
        'tipo_personeria',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}