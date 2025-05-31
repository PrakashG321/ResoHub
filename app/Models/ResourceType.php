<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceType extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceTypesFactory> */
    use HasFactory;

    protected $fillable = [
        "name"
    ];

    public function resources(): HasMany
    {
        return $this->HasMany(Resource::class);
    }
}
