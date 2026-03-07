<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'bio',
        'location',
        'profile_visibility',
        'notification_settings',
        'email',
        'password',
    ];

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
            'profile_visibility' => 'array',
            'notification_settings' => 'array',
        ];
    }

    public function favoriteLakes(): BelongsToMany
    {
        return $this->belongsToMany(Lake::class, 'lake_user_favorites')
            ->withTimestamps();
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function iceReports(): HasMany
    {
        return $this->hasMany(IceReport::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function wantsMentionNotifications(): bool
    {
        $settings = $this->notification_settings ?? [];
        return $settings['mentions'] ?? true;
    }
}
