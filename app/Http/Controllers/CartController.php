<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        try {
             $carts = Cart::with([
                'cartItems.product.category',
                'user'
            ])
            ->where('user_id', $user->id)
            ->get();

            if($carts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data keranjang',
                'data' => $carts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try{
            //cek apakah ada keranjang yang aktif
            $cart = Cart::FirstorCreate([
                'user_id' => $user->id,
                'status' => 'tertunda'
            ]);

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong',
                ], 404);
            }

            $existingItem = $cart->cartItems()->where('product_id', $request->product_id)->first();
            
            if ($existingItem) {
                $existingItem->quantity = $request->quantity;
                $existingItem->subtotal = $existingItem->price_at_checkout * $request->quantity;
                $existingItem->save();
            } else {
                $product = Product::findOrFail($request->product_id);
                $cart->cartItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price_at_checkout' => $product->price,
                    'subtotal' => $product->price * $request->quantity
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan atau memperbarui item ke keranjang',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ]);
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
        $user = $request->user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try{
            $cart = Cart::where('id', $id)->where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong',
                ], 404);
            }

            $cartItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tidak ditemukan',
                ], 404);
            }

            $cartItem->quantify = $request->quantity;
            $cartItem->subtotal = $cartItem->price_at_checkout * $request->quantity;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memperbarui item di keranjang',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        $user = $request->user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try{
            $cart = Cart::where('id', $id)->where('user_id', $user->id)->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong',
                ], 404);
            }

            $cartItem = $cart->cartItems()->where('product_id', $request->product_id)->first();

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item tidak ditemukan',
                ], 404);
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus item di keranjang',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ]);
        }
    }
}
