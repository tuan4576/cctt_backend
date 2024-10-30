<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'category_id', 'photo', 'brand', 'name', 'description', 'details', 'price', 'discount', 'status',
    ];
    protected $table = 'products';
    public $timestamps = true;

    protected $casts = [
        'price' => 'float',
        'discount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Review');
    }

    public function stocks()
    {
        return $this->hasMany('App\Models\Stock');
    }
    public function wishlists()
    {
        return $this->hasMany('App\Models\Wishlist');
    }    
    

}
