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
            // Delete old avatar file if not a base64 string
            if ($user->avatar && !str_starts_with($user->avatar, 'data:image')) {
                \App\Services\FileStorageService::delete($user->avatar, 'avatars');
            }

            $user->avatar = \App\Services\FileStorageService::upload($request->file('avatar'), 'avatars');
            $user->save();
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
