<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OfflineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $offlineUsers = User::where('role', 'offline')->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data',
                'data' => $offlineUsers
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email sudah terdaftar atau ada kolom yang kosong, silahkan coba lagi.',
                    'errors' => $validator->errors()
                ], 422);
            }

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'offline'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambahkan pengguna offline',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $offline_user_id, Request $request)
    {
        try {
            $offline = User::find($offline_user_id);

            if (!$offline) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna offline tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'data' => $offline
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $offline_user_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $offline_user_id,
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email sudah terdaftar atau ada kolom yang kosong, silahkan coba lagi.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $offline = User::find($offline_user_id);

            if (!$offline) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna offline tidak ditemukan',
                ], 404);
            }

            $offline->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memperbarui pengguna offline',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $offline_user_id, Request $request)
    {
        try {
            $offline = User::find($offline_user_id);

            if (!$offline) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna offline tidak ditemukan',
                ], 404);
            }

            $offline->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus pengguna offline',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
