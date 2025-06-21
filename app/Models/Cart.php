<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'status',
        'added_at',
    ];

    public function cartItems() {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function products() {
        return $this->belongsToMany(Product::class, 'cart_items', 'cart_id', 'product_id')->withPivot('quantity', 'price_at_checkout', 'subtotal');
    }
}
