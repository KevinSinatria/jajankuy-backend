<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OfflineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengakses data'
            ], 403);
        }

        try {
            $offline = User::where('role', 'offline')->get();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data',
                'data' => $offline
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
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengakses data'
            ], 403);
        }

        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
            ]);

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
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengakses data'
            ], 403);
        }

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
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengakses data'
            ], 403);
        }

        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
            ]);

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
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengakses data'
            ], 403);
        }

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
