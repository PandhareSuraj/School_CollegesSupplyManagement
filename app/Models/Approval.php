<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    protected $fillable = ['stationary_request_id', 'user_id', 'role', 'status', 'comments'];

    public function stationaryRequest(): BelongsTo
    {
        return $this->belongsTo(StationaryRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
