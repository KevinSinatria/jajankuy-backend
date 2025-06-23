<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserProfileController extends Controller
{
    public function get(Request $request){
        $user = $request->user();

         if (!$user){
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);
    }

    public function update(Request $request){
        $user = $request->user();

         if (!$user){
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $validated['name']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil anda berhasil diperbarui',
        ]);
    }

    public function delete(Request $request){
        $user = $request->user();

         if (!$user){
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login, silahkan login terlebih dahulu.',
                'errors' => null
            ], 401);
        }
        
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun anda berhasil dihapus',
        ]);
    }

}
