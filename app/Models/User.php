<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_number',
        'password','role', 'status'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Check if the user has a specific role
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
