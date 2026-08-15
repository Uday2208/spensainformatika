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
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Save to public_path
            $file->move(public_path('uploads/avatars'), $filename);

            $pubPath = public_path('uploads/avatars/' . $filename);

            // Copy to base_path/uploads/avatars if exists
            $baseUploadDir = base_path('uploads/avatars');
            if (!\Illuminate\Support\Facades\File::exists($baseUploadDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($baseUploadDir, 0755, true);
            }
            if (\Illuminate\Support\Facades\File::exists($pubPath)) {
                @copy($pubPath, $baseUploadDir . '/' . $filename);
            }

            // Copy to public_html/uploads/avatars if exists
            $htmlUploadDir = base_path('public_html/uploads/avatars');
            if (\Illuminate\Support\Facades\File::exists($htmlUploadDir) && \Illuminate\Support\Facades\File::exists($pubPath)) {
                @copy($pubPath, $htmlUploadDir . '/' . $filename);
            }

            // Delete old avatar from all paths
            if ($user->avatar) {
                $pathsToDelete = [
                    public_path('uploads/avatars/' . $user->avatar),
                    base_path('uploads/avatars/' . $user->avatar),
                    base_path('public_html/uploads/avatars/' . $user->avatar),
                ];
                foreach ($pathsToDelete as $path) {
                    if (\Illuminate\Support\Facades\File::exists($path)) {
                        @\Illuminate\Support\Facades\File::delete($path);
                    }
                }
            }

            $user->avatar = $filename;
            $user->save();
        }

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }
}
