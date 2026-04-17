<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FelDocument extends Model
{
    protected $table = 'fel_documents';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'doc_type',
        'environment',
        'fel_status',
        'uuid',
        'series',
        'number',
        'issued_at',
        'receiver_taxid',
        'receiver_name',
        'total_amount_q',
        'request_payload',
        'response_payload',
        'xml',
        'pdf',
        'html',
        'error_message',
        'created_by',
        'idp_amount_q',
        'vat_amount_q',
        'taxable_base_q',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'total_amount_q' => 'decimal:2',
        'idp_amount_q' => 'decimal:2',
        'vat_amount_q' => 'decimal:2',
        'taxable_base_q' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function events()
    {
        return $this->hasMany(FelEvent::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }   
}
