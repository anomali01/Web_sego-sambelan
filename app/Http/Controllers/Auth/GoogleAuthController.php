<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth authorization page.
     */
    public function redirectToGoogle()
    {
        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URL'),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
            'prompt' => 'select_account'
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Obtain the user information from Google and log the user in.
     */
    public function handleGoogleCallback(Request $request)
    {
        // 1. Verify CSRF State
        $state = session()->pull('google_oauth_state');
        if (empty($state) || $state !== $request->state) {
            return redirect('/login')->with('error', 'Autentikasi gagal: Token keamanan tidak cocok.');
        }

        // 2. Check if auth code is present
        $code = $request->code;
        if (empty($code)) {
            return redirect('/login')->with('error', 'Autentikasi dibatalkan oleh pengguna.');
        }

        try {
            // 3. Exchange Auth Code for Access Token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uri' => env('GOOGLE_REDIRECT_URL'),
                'grant_type' => 'authorization_code',
            ]);

            if ($tokenResponse->failed()) {
                return redirect('/login')->with('error', 'Gagal mendapatkan token akses dari Google.');
            }

            $accessToken = $tokenResponse->json('access_token');

            // 4. Fetch User Profile Info using Access Token
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($userResponse->failed()) {
                return redirect('/login')->with('error', 'Gagal mengambil informasi profil dari Google.');
            }

            $googleUser = $userResponse->json();
            $email = $googleUser['email'] ?? null;
            $name = $googleUser['name'] ?? 'Google User';

            if (empty($email)) {
                return redirect('/login')->with('error', 'Email tidak ditemukan dari akun Google Anda.');
            }

            // 5. Look up or Create User in local database
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Register a new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)), // Random secure password since they use Google
                    'role' => 'buyer', // Default role for Google login
                ]);

                Auth::login($user, true); // Log in with remember cookie set to true

                return redirect('/profile/complete')
                    ->with('success', 'Pendaftaran berhasil! Silakan lengkapi profil Anda.');
            }

            // Log in existing user
            Auth::login($user, true); // Log in with remember cookie set to true

            if ($user->isSeller()) {
                return redirect('/admin/dashboard')->with('success', 'Selamat datang kembali, Penjual! 🔥');
            }

            return redirect('/menu')->with('success', 'Selamat datang kembali! 🍛');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Terjadi kesalahan sistem saat menghubungi Google.');
        }
    }
}
