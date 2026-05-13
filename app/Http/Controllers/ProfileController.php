<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show the profile completion form.
     */
    public function showCompleteForm(Request $request)
    {
        $profile = $request->user()->profile;

        return view('profile.complete', compact('profile'));
    }

    /**
     * Update or create the buyer's profile.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'street_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        Profile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return redirect('/menu')
            ->with('success', 'Profil berhasil dilengkapi! Selamat menikmati menu kami.');
    }
}
