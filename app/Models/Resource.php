<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourcesFactory> */
    use HasFactory;

    protected $fillable = [
        "name",
        "resource_type_id",
        "description",
        "capacity",
        "is_available"
    ];

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function resource_type(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }
}
