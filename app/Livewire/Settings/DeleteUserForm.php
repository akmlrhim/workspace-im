<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public string $confirmText = '';

    #[Computed]
    public function hasPassword(): bool
    {
        return ! is_null(Auth::user()->password);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        if ($this->hasPassword) {
            $this->validate([
                'password' => $this->currentPasswordRules(),
            ], [
                'password.required' => 'Password wajib diisi.',
                'password.current_password' => 'Password tidak sesuai.',
            ]);
        } else {
            $this->validate([
                'confirmText' => ['required', 'in:HAPUS'],
            ], [
                'confirmText.required' => 'Teks konfirmasi wajib diisi.',
                'confirmText.in' => 'Ketik HAPUS untuk mengonfirmasi.',
            ]);
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
