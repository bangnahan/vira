@extends('layouts.app')

@section('title', 'Login Administrator — VIRA Backoffice')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden">
    <!-- Subtle Background Glow Effects -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-[#FF5500]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 left-1/2 -translate-x-1/2 translate-y-1/4 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Brand & Security Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-600 via-[#FF5500] to-amber-500 shadow-xl shadow-rose-950/50 mb-4 group ring-4 ring-rose-500/20">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[11px] font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Restricted Access Portal</span>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-white font-athletic tracking-wide">
                LOGIN ADMIN VIRA
            </h1>
            <p class="text-xs text-slate-400 mt-1.5 max-w-xs mx-auto">
                Autentikasi aman pengelola event, verifikasi e-BIB, e-Sertifikat, dan integrasi Meta CAPI.
            </p>
        </div>

        <!-- Session Flash Messages -->
        @if(session('info'))
            <div class="mb-5 p-3.5 rounded-xl bg-cyan-950/70 border border-cyan-500/40 text-cyan-200 text-xs flex items-center gap-2.5 shadow-lg">
                <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/70 border border-emerald-500/40 text-emerald-200 text-xs flex items-center gap-2.5 shadow-lg">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-xs space-y-1 shadow-xl">
                <div class="flex items-center gap-2 font-bold text-rose-300">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Gagal Mengakses Panel Admin</span>
                </div>
                @foreach($errors->all() as $error)
                    <p class="pl-6 text-rose-300/90">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Login Card Form -->
        <div class="bg-slate-900/90 backdrop-blur-2xl border border-slate-800 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl shadow-black/80">
            <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-5" autocomplete="on">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        Alamat Email Admin
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               required 
                               autofocus
                               value="{{ old('email') }}"
                               placeholder="nama@domain.com"
                               class="w-full bg-slate-950/80 border {{ $errors->has('email') ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-700/80 focus:border-[#FF5500] focus:ring-[#FF5500]/30' }} rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 transition focus:outline-none focus:ring-2">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="••••••••••••"
                               class="w-full bg-slate-950/80 border {{ $errors->has('email') ? 'border-rose-500 focus:ring-rose-500' : 'border-slate-700/80 focus:border-[#FF5500] focus:ring-[#FF5500]/30' }} rounded-xl pl-11 pr-11 py-3 text-sm text-white placeholder-slate-500 transition focus:outline-none focus:ring-2">
                        
                        <!-- Toggle Password Visibility -->
                        <button type="button" 
                                id="togglePasswordBtn" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition"
                                title="Tampilkan/Sembunyikan Kata Sandi">
                            <svg id="eyeIconOpen" class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" 
                               name="remember" 
                               value="1"
                               class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-[#FF5500] focus:ring-[#FF5500] focus:ring-offset-slate-900 transition cursor-pointer">
                        <span class="text-xs text-slate-300">Ingat sesi saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-5 rounded-xl font-bold uppercase tracking-wider text-sm text-white bg-gradient-to-r from-[#FF5500] via-[#FF6600] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/60 glow-orange flex items-center justify-center gap-2 font-athletic text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Masuk ke Panel Admin</span>
                </button>
            </form>

            <!-- Security Info Badges -->
            <div class="mt-6 pt-5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>256-bit SSL Session</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Brute-force Protected</span>
                </div>
            </div>
        </div>

        <!-- Back to Website Link -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs text-slate-400 hover:text-white transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Website Utama VIRA</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClose = document.getElementById('eyeIconClose');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                if (isPassword) {
                    eyeOpen.classList.add('hidden');
                    eyeClose.classList.remove('hidden');
                } else {
                    eyeOpen.classList.remove('hidden');
                    eyeClose.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
@endsection
