<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function getFavorite(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        // Ambil data favorite sebagai koleksi
        $favorites = $user->favorites()->with('category')->get();

        if ($favorites->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum menambahkan favorite',
                'errors' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data favorite',
            'data' => $favorites
        ]);
    }

    public function addToFavorite(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
                'errors' => null
            ], 404);
        }
        
        if (!$user->favorites()->where('product_id', $product->id)->exists()) {
            $user->favorites()->attach($product->id);
        }

        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan ke favorite',
        ]);
    }

   public function deleteFromFavorite(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        $exists = $user->favorites()->where('product_id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan di favorite',
                'errors' => null
            ], 404);
        }

        $user->favorites()->detach($id);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus favorite',
        ]);
    }

}
