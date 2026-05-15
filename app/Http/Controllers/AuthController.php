<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1) Validasi field wajib diisi.
        //    Kalau gagal, Laravel otomatis: redirect back, flash $errors, dan
        //    preserve old() input (kecuali field password — by default tidak di-flash).
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $username = $request->username;
        $password = $request->password;

        // 2) Cari user berdasarkan username.
        //    PENTING: MySQL default pakai collation case-insensitive (utf8mb4_*_ci),
        //    jadi 'admin' akan match 'ADMIN'. Untuk memaksa case-sensitive,
        //    kita ambil semua kandidat lalu cocokkan persis di PHP pakai strcmp().
        //    Pakai strcmp (bukan ===) agar perbandingan binary-safe.
        $user = User::where('username', $username)
            ->get()
            ->first(fn($u) => strcmp($u->username, $username) === 0);

        // 3) Verifikasi kredensial.
        //    Pesan error sengaja generik (tidak bedakan "username tidak ada" vs
        //    "password salah") untuk mencegah user enumeration attack.
        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi salah.',
                'password' => ' ', // spasi: trigger border merah tanpa pesan duplikat
            ])->redirectTo('/login');
        }

        // 4) Cek status user
        if (isset($user->status) && $user->status === 'Nonaktif') {
            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
            ])->redirectTo('/login');
        }

        // 5) Login user secara manual karena kita sudah verifikasi sendiri
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('auth.user_id', $user->id_user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}