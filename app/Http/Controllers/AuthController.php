<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            RateLimiter::clear('login:'.$request->input('username').'|'.$request->ip());
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'guru') {
            return redirect('/app/dashboard-guru');
        } elseif ($user->role === 'siswa') {
            return redirect('/app/dashboard-siswa');
        }
        return redirect('/');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096'
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            try {
                // Upload baru DULU sebelum menghapus yang lama
                $newAvatarPath = \App\Services\FileStorageService::upload($request->file('avatar'), 'avatars');

                // Simpan referensi avatar lama
                $oldAvatar = $user->avatar;

                // Simpan avatar baru ke database
                $user->avatar = $newAvatarPath;
                $user->save();

                // Hapus avatar lama SETELAH avatar baru berhasil tersimpan
                if ($oldAvatar && !str_starts_with($oldAvatar, 'data:image') && !str_starts_with($oldAvatar, 'http')) {
                    \App\Services\FileStorageService::delete($oldAvatar, 'avatars');
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Foto profil berhasil diperbarui.',
                        'avatar_url' => $user->avatar_url
                    ]);
                }

                return back()->with('success', 'Foto profil berhasil diperbarui.');
            } catch (\Exception $e) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal mengunggah foto: ' . $e->getMessage()
                    ], 500);
                }
                return back()->with('error', 'Gagal mengunggah foto. Silakan coba lagi.');
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File foto tidak ditemukan.'
            ], 400);
        }

        return back()->with('error', 'File foto tidak diterima oleh server.');
    }
}
