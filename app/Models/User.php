<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    const ROLE_ADMIN = 'admin';
    const ROLE_REGISTERED = 'registered_user';
    const ROLE_UNREGISTERED = 'unregistered_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function isAdmin()
    {
         return $this->role === self::ROLE_ADMIN;
    }

    public function isRegistered(): bool
    {
     return $this->role === self::ROLE_REGISTERED;
    }

    public function isUnregistered(): bool
    {
    return $this->role === self::ROLE_UNREGISTERED;
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
