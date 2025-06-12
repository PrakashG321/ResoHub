<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\PersonalEmailVerificationNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'personal_email',
        'password',
    ];

    public function booking(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingLogs(): HasMany
    {
        return $this->hasMany(BookingLog::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Used by Laravel to send email verification
    public function sendEmailVerificationNotification()
    {
        $this->notify(new PersonalEmailVerificationNotification());
    }

    // Override: Send email to personal_email, not email
    public function getEmailForVerification()
    {
        return $this->personal_email;
    }


    public function routeNotificationForMail()
    {
        return $this->personal_email ?? $this->email;
    }


    protected static function booted()
    {
        static::deleting(function ($user) {
            // Detach all roles to remove related entries from model_has_roles
            $user->roles()->detach();
        });
    }
}
