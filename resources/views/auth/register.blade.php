@extends('layouts.app')
@section('title', 'Daftar - Sego Sambelan')

@section('content')
<div class="auth-page">
    <div class="auth-card glass-card">
        <div class="auth-header">
            <img src="{{ asset('images/icons/logo_utama.png') }}" alt="Sego Sambelan Logo" style="height: 70px; width: auto; object-fit: contain; margin-bottom: 0.5rem; filter: drop-shadow(0 6px 12px rgba(234, 88, 12, 0.25));">
            <h1>Buat Akun Baru</h1>
            <p>Bergabung dengan Sego Sambelan</p>
        </div>

        <form method="POST" action="/register" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus class="form-input @error('name') input-error @enderror">
                @error('name')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required class="form-input @error('email') input-error @enderror">
                @error('email')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required class="form-input @error('password') input-error @enderror">
                @error('password')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required class="form-input">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Daftar</button>
        </form>

        <div class="auth-divider" style="margin: 1.5rem 0; display: flex; align-items: center; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">
            <div style="flex: 1; height: 1px; background: rgba(232,184,75,0.25);"></div>
            <span style="padding: 0 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--primary);">atau</span>
            <div style="flex: 1; height: 1px; background: rgba(232,184,75,0.25);"></div>
        </div>

        <a href="/auth/google" class="btn btn-full" style="background: #ffffff; color: #1f2937; border: 1px solid #e5e7eb; font-weight: 700; box-shadow: 0 4px 15px rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255,255,255,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255,255,255,0.1)';">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            Daftar dengan Google
        </a>

        <p class="auth-footer-text">
            Sudah punya akun? <a href="/login" class="link-primary">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
