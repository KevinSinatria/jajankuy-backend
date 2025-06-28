<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customers = User::where('role', 'customer')->get();
        $notes = fake()->sentence();
        $is_paid = true;
        $total_price = fake()->randomFloat(2, 1000, 100000);
        $status = fake()->randomElement(['diproses', 'siap_diambil', 'selesai', 'dibatalkan']);

        return [
            'user_id' => $customers->random()->id,
            'user_name' => $customers->random()->name,
            'notes' => $notes,
            'is_paid' => $is_paid,
            'total_price' => $total_price,
            'status' => $status,
            'paid_at' => $status == 'selesai' ? now() : null,
            'cancelled_at' => $status == 'dibatalkan' ? now() : null
        ];
    }
}
