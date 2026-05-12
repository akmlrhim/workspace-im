<?php

namespace App\Models;

use App\Models\Project\TaskList;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar', 'email_verified_at', 'role', 'position'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function taskLists(): BelongsToMany
    {
        return $this->belongsToMany(TaskList::class, 'task_list_user')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_user', 'administrator']) || $this->isFirstUser();
    }

    public function isSuperUser(): bool
    {
        return $this->role === 'super_user' || $this->isFirstUser();
    }

    public function canManageAllProjects(): bool
    {
        return in_array($this->role, ['super_user', 'administrator']) || $this->isFirstUser();
    }

    public function canManageLists(): bool
    {
        return in_array($this->role, ['super_user', 'administrator']) || $this->isFirstUser();
    }

    /**
     * Check if this is the first registered user (fallback super-admin).
     * Uses once() to run the SELECT MIN(id) query at most once per request,
     * regardless of how many times the role-check methods are called.
     */
    private function isFirstUser(): bool
    {
        return (int) $this->id === once(fn (): int => (int) static::query()->min('id'));
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isGuest(): bool
    {
        return $this->role === 'guest' || $this->position === null;
    }

    public static function roles(): array
    {
        return [
            'super_user' => 'Super User',
            'administrator' => 'Administrator',
            'manager' => 'Manager',
            'member' => 'Member',
            'guest' => 'Guest (Pending)',
        ];
    }

    public static function positions(): array
    {
        return [
            'CEO',
            'Finance',
            'HR',
            'Admin Operasional',
            'Desain Grafis',
            'Video Editor',
            'Web Developer',
            'Performance Marketer',
            'Advertiser',
            'SMS',
            'BDS',
            'DMS',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
