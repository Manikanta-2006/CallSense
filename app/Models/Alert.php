<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'call_id', 'user_id', 'type', 'status', 'severity',
        'description', 'resolution_notes', 'resolved_by',
        'resolved_at', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'investigating']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
