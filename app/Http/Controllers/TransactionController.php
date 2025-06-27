<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $transactions = Transaction::with(['user'])->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data',
                'data' => $transactions
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:masuk,keluar',
                'amount' => 'required|numeric',
                'description' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang anda masukkan invalid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $transaction = Transaction::create([
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'user_id' => request()->user()->id,
                'transaction_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan data transaksi.',
                'data' => $transaction
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
        try {
            $transaction = Transaction::with(['user'])->find($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data',
                'data' => $transaction
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
            'description' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang anda masukkan invalid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            if ($request->has('description')) {
                $transaction->update([
                    'description' => $request->description
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah data',
                'data' => $transaction
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => (object) []
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                    'errors' => (object) []
                ], 404);
            }

            if ($transaction->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil menghapus data',
                    'data' => $transaction
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
