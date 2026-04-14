<?php

namespace App\Livewire\Users;

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('User Management')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterRole = '';

    // Create form
    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createRole = 'member';

    public string $createPosition = '';

    public bool $createPositionCustom = false;

    public string $createPassword = '';

    public string $createPasswordConfirmation = '';

    // Edit form
    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public string $editRole = 'member';

    public string $editPosition = '';

    public bool $editPositionCustom = false;

    public string $editPassword = '';

    public string $editPasswordConfirmation = '';

    // Delete
    public bool $showDeleteConfirm = false;

    public ?int $deletingUserId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRole(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function selectCreatePosition(string $value): void
    {
        if ($value === '__other__') {
            $this->createPositionCustom = true;
            $this->createPosition = '';
        } else {
            $this->createPositionCustom = false;
            $this->createPosition = $value;
        }
    }

    public function clearCreatePositionCustom(): void
    {
        $this->createPositionCustom = false;
        $this->createPosition = '';
    }

    public function selectEditPosition(string $value): void
    {
        if ($value === '__other__') {
            $this->editPositionCustom = true;
            $this->editPosition = '';
        } else {
            $this->editPositionCustom = false;
            $this->editPosition = $value;
        }
    }

    public function clearEditPositionCustom(): void
    {
        $this->editPositionCustom = false;
        $this->editPosition = '';
    }

    public function createUser(): void
    {
        $this->authorize('manage-users');

        $validated = $this->validate([
            'createName' => 'required|string|max:255',
            'createEmail' => 'required|email|max:255|unique:users,email',
            'createRole' => 'required|in:super_user,administrator,member',
            'createPosition' => 'nullable|string|max:255',
            'createPassword' => 'required|string|min:8|confirmed',
            'createPasswordConfirmation' => 'required',
        ]);

        // Only super_user can assign super_user or administrator roles
        if (in_array($validated['createRole'], ['super_user', 'administrator']) && ! auth()->user()->isSuperUser()) {
            $validated['createRole'] = 'member';
        }

        $user = User::create([
            'name' => $validated['createName'],
            'email' => $validated['createEmail'],
            'password' => $validated['createPassword'],
            'email_verified_at' => now(),
        ]);

        $user->forceFill([
            'role' => $validated['createRole'],
            'position' => $validated['createPosition'] ?: null,
        ])->save();

        $this->resetCreateForm();
        $this->showCreateModal = false;

        Flux::toast('User berhasil dibuat.', variant: 'success');
    }

    public function editUser(int $userId): void
    {
        $this->authorize('manage-users');

        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRole = $user->role ?? 'member';
        $this->editPosition = $user->position ?? '';
        $this->editPositionCustom = filled($user->position) && ! in_array($user->position, User::positions());
        $this->editPassword = '';
        $this->editPasswordConfirmation = '';
        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->authorize('manage-users');

        $user = User::findOrFail($this->editingUserId);

        $rules = [
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
            'editRole' => 'required|in:super_user,administrator,member',
            'editPosition' => 'nullable|string|max:255',
        ];

        if (filled($this->editPassword)) {
            $rules['editPassword'] = 'required|string|min:8|confirmed';
            $rules['editPasswordConfirmation'] = 'required';
        }

        $validated = $this->validate($rules);

        $data = [
            'name' => $validated['editName'],
            'email' => $validated['editEmail'],
        ];

        if (filled($this->editPassword)) {
            $data['password'] = Hash::make($this->editPassword);
        }

        $user->update($data);

        // Prevent removing super_user role from the primary account
        $role = $validated['editRole'];
        if ($user->id === User::min('id') && $role !== 'super_user') {
            $role = 'super_user';
        }

        $user->forceFill([
            'role' => $role,
            'position' => $validated['editPosition'] ?: null,
        ])->save();

        $this->showEditModal = false;
        $this->editingUserId = null;

        Flux::toast('User berhasil diperbarui.', variant: 'success');
    }

    public function confirmDelete(int $userId): void
    {
        $this->authorize('manage-users');

        $this->deletingUserId = $userId;
        $this->showDeleteConfirm = true;
    }

    public function deleteUser(): void
    {
        $this->authorize('manage-users');

        $user = User::findOrFail($this->deletingUserId);

        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account from here.');
        abort_if($user->id === User::min('id'), 403, 'The primary admin account cannot be deleted.');

        $user->delete();

        $this->reset('deletingUserId', 'showDeleteConfirm');

        Flux::toast('User berhasil dihapus.', variant: 'danger');
    }

    private function resetCreateForm(): void
    {
        $this->reset('createName', 'createEmail', 'createPassword', 'createPasswordConfirmation', 'createPosition');
        $this->createRole = 'member';
        $this->createPositionCustom = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            }))
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->orderBy('id')
            ->paginate(15);

        return view('livewire.users.user-index', [
            'users' => $users,
            'roles' => User::roles(),
            'positions' => User::positions(),
        ]);
    }
}
