<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FelEvent extends Model
{
    public $timestamps = false;

    protected $table = 'fel_events';

    protected $fillable = [
        'fel_document_id',
        'event_type',
        'description',
        'payload',
        'response',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'created_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(FelDocument::class, 'fel_document_id');
    }
}
