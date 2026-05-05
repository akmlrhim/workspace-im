<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\User as ContractsUser;
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
        } catch (\Exception $e) {
            Log::warning('Google OAuth callback failed', ['message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => __('Autentikasi Google gagal. Silakan coba lagi.')]);
        }

        if (! $this->isVerifiedGoogleEmail($googleUser)) {
            return redirect()->route('login')->withErrors([
                'email' => __('Email Google belum diverifikasi. Gunakan akun Google dengan email terverifikasi.'),
            ]);
        }

        if ($linkingUserId = session()->pull('google.linking_user_id')) {
            return $this->handleLinking((int) $linkingUserId, $googleUser);
        }

        return $this->handleLogin($googleUser);
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

    /**
     * Handle authenticated user linking a Google account to their profile.
     */
    private function handleLinking(int $linkingUserId, ContractsUser $googleUser)
    {
        if (! auth()->check() || (int) auth()->id() !== $linkingUserId) {
            Log::warning('Google link rejected: session user mismatch', [
                'linking_user_id' => $linkingUserId,
                'auth_id' => auth()->id(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => __('Sesi tautan Google tidak valid. Silakan coba lagi.'),
            ]);
        }

        $user = User::findOrFail($linkingUserId);

        $googleIdTaken = User::where('google_id', $googleUser->getId())
            ->where('id', '!=', $user->id)
            ->exists();

        if ($googleIdTaken) {
            return redirect()->route('profile.edit')->withErrors([
                'google' => __('Akun Google ini sudah terhubung dengan user lain.'),
            ]);
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $this->storeGoogleAvatar($googleUser),
        ]);

        return redirect()->route('profile.edit')->with('status', 'google-linked');
    }

    /**
     * Handle login / registration via Google.
     */
    private function handleLogin(ContractsUser $googleUser)
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $emailExists = User::where('email', $googleUser->getEmail())->exists();

            if ($emailExists) {
                Log::warning('Google login rejected: email exists without linked google_id', [
                    'email' => $googleUser->getEmail(),
                ]);

                return redirect()->route('login')->withErrors([
                    'email' => __('Email ini sudah terdaftar tanpa koneksi Google. Silakan login dengan password terlebih dahulu, lalu hubungkan akun Google Anda dari halaman Profile.'),
                ]);
            }

            $user = User::create([
                'name' => $googleUser->getName() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $this->storeGoogleAvatar($googleUser),
                'role' => 'guest',
                'position' => null,
                'email_verified_at' => now(),
            ]);
        } elseif (! $user->avatar || str_contains((string) $user->avatar, 'googleusercontent.com')) {
            $user->update(['avatar' => $this->storeGoogleAvatar($googleUser)]);
        }

        if (is_null($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $lastUrl = session()->pull('last_visited_url');

        return redirect()->intended($lastUrl ?: route('dashboard'));
    }

    /**
     * Download the Google profile photo and store it locally.
     * Returns the local public URL, or null on failure.
     */
    private function storeGoogleAvatar(ContractsUser $googleUser): ?string
    {
        $url = $googleUser->getAvatar();

        if (! $url) {
            return null;
        }

        // Request a larger photo (400px instead of the default 96px)
        $url = preg_replace('/=s\d+-c$/', '=s400-c', $url);

        try {
            $response = Http::timeout(10)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $filename = 'avatars/google_'.$googleUser->getId().'.jpg';
            Storage::disk('public')->put($filename, $response->body());

            return Storage::disk('public')->url($filename);
        } catch (\Exception $e) {
            Log::warning('Failed to download Google avatar', [
                'google_id' => $googleUser->getId(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Confirm Google asserts the email is verified.
     */
    private function isVerifiedGoogleEmail(ContractsUser $googleUser): bool
    {
        $raw = $googleUser->getRaw();

        return ($raw['email_verified'] ?? false) === true
            && ! empty($googleUser->getEmail());
    }
}
