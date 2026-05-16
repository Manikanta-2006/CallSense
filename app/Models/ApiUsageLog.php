<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'user_id', 'service', 'endpoint', 'tokens_used',
        'success', 'error_message', 'response_time_ms',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
