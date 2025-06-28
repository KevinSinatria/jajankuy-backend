<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderId = Order::pluck('id')->random();
        $product = Product::all()->random();
        $productId = $product->id;
        $quantity = fake()->numberBetween(1, 100);
        $priceAtPurchase = $product->price;
        $subtotal = $quantity * $priceAtPurchase;

        $order = Order::find($orderId);
        $order->total_price += $subtotal;
        $order->save();

        return [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_at_purchase' => $priceAtPurchase,
            'subtotal' => $subtotal
        ];
    }
}
