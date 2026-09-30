<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login admin.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isRaceAdmin()) {
            return redirect()->route('admin.events.index');
        }

        return view('admin.auth.login');
    }

    /**
     * Proses autentikasi login admin dengan proteksi brute force (rate limiting).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = 'admin-login|'.Str::lower($validated['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk yang salah. Demi keamanan, silakan tunggu {$seconds} detik sebelum mencoba lagi.",
            ]);
        }

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        }

        $user = Auth::user();

        // Verifikasi hak akses admin
        if (! $user || ! $user->isRaceAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Akses ditolak. Akun Anda tidak memiliki hak akses administrator.',
            ]);
        }

        // Berhasil login: bersihkan rate limiter dan regenerasi session ID (mencegah session fixation)
        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.events.index'))
            ->with('success', 'Selamat datang kembali di panel admin, '.$user->name.'!');
    }

    /**
     * Hancurkan sesi autentikasi admin (Logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('info', 'Sesi administrator berhasil diakhiri dengan aman.');
    }
}
