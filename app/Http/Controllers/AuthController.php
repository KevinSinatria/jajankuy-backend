<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
           'email' => 'required|email',
           'password' => 'required' 
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)){
            return response()->json([
                'success' => false,
                'message' => 'Email atau password anda salah, silahkan coba lagi',
                'errors' => null
            ], 401);
        }

        $token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'message' => "Anda telah berhasil login",
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 200);
    }

    public function register(Request $request){
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang anda masukkan invalid',
                'message' => 'Data yang anda masukkan invalid',
                'errors' => [
                    'email' => ['Email sudah didaftarkan oleh pengguna lain.'],
                    'password' => ['Password dan validasi password tidak sama.']
                ]
            ], 422);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil didaftarkan',
        ]);
    }

    public function logout(Request $request){
        $user = $request->user();

        if (!$user){
            return response()->json()([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anda telah berhasil logout'
        ], 200);
    }
}
