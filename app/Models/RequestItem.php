<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    protected $fillable = ['stationary_request_id', 'product_id', 'quantity', 'unit_price', 'total_price'];

    public function stationaryRequest(): BelongsTo
    {
        return $this->belongsTo(StationaryRequest::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
