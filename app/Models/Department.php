<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['college_id', 'name', 'code'];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stationaryRequests(): HasMany
    {
        return $this->hasMany(StationaryRequest::class);
    }
}
