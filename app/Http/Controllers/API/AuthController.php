<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'schoolName' => 'required|string|max:255',
                'position' => 'required|string|in:kepala-sekolah,wakil-kepala,guru,tata-usaha,admin',
                'password' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->fullName,
                'full_name' => $request->fullName,
                'email' => $request->email,
                'school_name' => $request->schoolName,
                'position' => $request->position,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user' => $user
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);

            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // Buat token untuk autentikasi
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'school_name' => $user->school_name,
                    'position' => $user->position
                ],
                'token' => $token
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete(); // Hapus semua token user

            return response()->json([
                'message' => 'Logout berhasil'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
}
