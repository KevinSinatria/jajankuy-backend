<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CustomerProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');

        $query = Product::with('category');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($category) {
            $categoryId = Category::where('slug', $category)->first()->id;
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->first();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data',
            'data' => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
