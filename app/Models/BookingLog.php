<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingLog extends Model
{
    /** @use HasFactory<\Database\Factories\BookingLogsFactory> */
    use HasFactory;

    protected $fillable = [
        "booking_id",
        "status",
        "changed_by",
        "comment"
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
