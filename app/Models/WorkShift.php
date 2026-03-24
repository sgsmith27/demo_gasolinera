<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'status',
        'opening_cash_q',
        'opening_notes',
        'closing_notes',
        'opened_by',
        'closed_by',
        'expected_cash_q',
        'delivered_cash_q',
        'cash_difference_q',
        'assignment_mode',
        'pump_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'opening_cash_q' => 'decimal:2',
        'expected_cash_q' => 'decimal:2',
        'delivered_cash_q' => 'decimal:2',
        'cash_difference_q' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'shift_id');
    }

    public function pump()
    {
        return $this->belongsTo(\App\Models\Pump::class);
    }
}