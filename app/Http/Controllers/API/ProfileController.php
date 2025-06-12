<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:500',
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'Profil berhasil diperbarui',
                'user' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai'
                ], 403);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'message' => 'Password berhasil diubah'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengubah password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadPhoto(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            // Simpan file
            $path = $request->file('photo')->store('profile-photos', 'public');

            // Hapus foto lama jika ada
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Update ke database
            $user->profile_photo_path = $path;
            $user->save();

            return response()->json([
                'message' => 'Foto profil berhasil diperbarui',
                'photo_url' => asset('storage/' . $path)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal upload foto profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
