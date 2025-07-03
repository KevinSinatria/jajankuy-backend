<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function orderItems()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id')->withPivot('quantity', 'price_at_purchase', 'subtotal');
    }

    public function carts() {
        return $this->belongsToMany(Cart::class, 'cart_items', 'product_id', 'cart_id')->withPivot('quantity', 'price_at_checkout', 'subtotal');
    }

    public function cartItems() {
        return $this->hasMany(CartItem::class, 'product_id', 'id');
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id');
    }
}
