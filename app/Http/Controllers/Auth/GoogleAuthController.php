<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function redirectForLink()
    {
        session(['google.linking_user_id' => auth()->id()]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors(['email' => __('Autentikasi Google gagal. Silakan coba lagi.')]);
        }

        if ($linkingUserId = session()->pull('google.linking_user_id')) {
            $user = User::findOrFail($linkingUserId);
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            return redirect()->route('profile.edit')->with('status', 'google-linked');
        }

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user, remember: true);

        $lastUrl = session()->pull('last_visited_url');

        return redirect()->intended($lastUrl ?: route('dashboard'));
    }

    public function unlink()
    {
        $user = auth()->user();

        if (is_null($user->password)) {
            return redirect()->route('profile.edit')
                ->withErrors(['google' => __('Anda harus set password terlebih dahulu sebelum memutuskan koneksi Google.')]);
        }

        $user->update([
            'google_id' => null,
            'avatar' => null,
        ]);

        return redirect()->route('profile.edit')->with('status', 'google-unlinked');
    }
}
