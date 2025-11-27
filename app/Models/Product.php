<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'rating',
        'extension'
    ];
    public function carts() 
    { 
        return $this->hasMany(Cart::class); 
    }
    
    public function order_items() 
    { 
        return $this->hasMany(OrderItem::class); 
    }
}
