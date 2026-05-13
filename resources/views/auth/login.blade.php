@extends('layouts.app')
@section('title', 'Masuk - Sego Sambelan')

@section('content')
<div class="auth-page">
    <div class="auth-card glass-card">
        <div class="auth-header">
            <span class="auth-logo">🔥</span>
            <h1>Selamat Datang</h1>
            <p>Masuk ke akun Sego Sambelan Anda</p>
        </div>

        <form method="POST" action="/login" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus class="form-input @error('email') input-error @enderror">
                @error('email')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required class="form-input @error('password') input-error @enderror">
                @error('password')
                <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-check">
                <label class="check-label">
                    <input type="checkbox" name="remember" class="check-input"> Ingat saya
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Masuk</button>
        </form>

        <p class="auth-footer-text">
            Belum punya akun? <a href="/register" class="link-primary">Daftar sekarang</a>
        </p>
    </div>
</div>
@endsection
