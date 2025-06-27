<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'notes' => 'string|sometimes',
                'user_name' => 'string|sometimes|max:255',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang anda masukkan invalid (harus berupa string)',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $user = $request->user()->load('carts.cartItems.product');
            $currentUserCart = $user->carts->where('status', 'tertunda')->first();
    
            $order = Order::create([
                'user_id' => $user->id,
                'user_name' => $request->user_name ?? $user->name,
                'notes' => $request->notes,
                'is_paid' => false,
                'total_price' => $currentUserCart->total_price,
                'status' => 'diproses',
                'paid_at' => null,
                'cancelled_at' => null,
            ]);
    
            foreach ($currentUserCart->cartItems as $cartItem) {
                OrderItem::create([ 
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price_at_purchase' => $cartItem->price_at_checkout,
                    'subtotal' => $cartItem->subtotal
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat.',
                'data' => $order
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $order = Order::find($id);

            if(!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            $order->update([
                'status' => 'dibatalkan',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan.',
                'data' => $order
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
