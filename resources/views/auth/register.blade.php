@extends('layouts.app')
@section('title', 'Daftar - Sego Sambelan')

@section('content')
<div class="auth-page">
    <div class="auth-card glass-card">
        <div class="auth-header">
            <span class="auth-logo">🔥</span>
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

        <p class="auth-footer-text">
            Sudah punya akun? <a href="/login" class="link-primary">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
