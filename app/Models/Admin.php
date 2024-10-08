<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject
{
    // use Notifiable;

    // protected $table = 'users'; // Use the same 'users' table

    // protected $fillable = [
    //     'name', 'email', 'photo', 'password', 'address_id', 'background', 'role_id',
    // ];

    // protected $hidden = [
    //     'password', 'remember_token',
    // ];

    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }

    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }

    // public function products()
    // {
    //     return $this->hasMany('App\Models\Product');
    // }

    // public function cartItems()
    // {
    //     return $this->hasMany('App\Models\ShoppingCart');
    // }

    // public function wishlistProducts()
    // {
    //     return $this->hasMany('App\Models\Wishlist');
    // }

    // public function addresses()
    // {
    //     return $this->hasMany('App\Models\Address');
    // }

    // // Scope to only get admin users
    // public function scopeAdmins($query)
    // {
    //     return $query->where('role_id', 1); // Assuming role_id 1 is for admins
    // }
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'photo', 'password', 'address_id', 'background',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function cartItems()
    {
        return $this->hasMany('App\Models\ShoppingCart');
    }

    public function wishlistProducts()
    {
        return $this->hasMany('App\Models\Wishlist');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }
}
