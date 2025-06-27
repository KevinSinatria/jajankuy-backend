<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['orderItems', 'user'])->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data',
            'data' => $orders
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
    public function show(string $id)
    {
        try {
            $order = Order::with(['orderItems.product', 'user'])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data',
                'data' => $order
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255|in:diproses,siap_diambil,selesai,dibatalkan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang anda masukkan invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            if ($request->status == 'diproses') {
                $order->update([
                    'status' => 'diproses'
                ]);
            }

            if ($request->status == 'siap_diambil') {
                $order->update([
                    'status' => 'siap_diambil'
                ]);
            }

            if ($request->status == 'dibatalkan') {
                $order->update([
                    'status' => 'dibatalkan',
                    'cancelled_at' => now()
                ]);
            }

            if ($request->status == 'selesai') {
                $order->update([
                    'status' => 'selesai',
                    'paid_at' => now(),
                    'is_paid' => true
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah data',
                'data' => $order
            ], 200);
        } catch (Exception $e) {
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
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            if ($order->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil menghapus data',
                    'data' => $order
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 500);
        }
    }
}
