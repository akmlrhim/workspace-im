<?php

use App\Livewire\NavbarNotifications;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'administrator']);
    $this->workspace = Workspace::create(['name' => 'WS', 'owner_id' => $this->admin->id]);
    $this->space = $this->workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
});

test('renders notification bell with unread count', function () {
    $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'data' => ['title' => 'Test', 'message' => 'Hello'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(NavbarNotifications::class)
        ->assertViewHas('unreadCount', 1)
        ->assertSeeHtml('bg-rose-500');
});

test('marks single notification as read', function () {
    $notif = $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'data' => ['title' => 'Test', 'message' => 'Hello'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(NavbarNotifications::class)
        ->call('markAsRead', $notif->id)
        ->assertViewHas('unreadCount', 0);

    expect($notif->fresh()->read_at)->not->toBeNull();
});

test('marks all notifications as read', function () {
    $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'data' => ['title' => 'A'],
    ]);
    $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\Notifications\TestNotification',
        'data' => ['title' => 'B'],
    ]);

    Livewire::actingAs($this->admin)
        ->test(NavbarNotifications::class)
        ->call('markAllAsRead')
        ->assertViewHas('unreadCount', 0);

    expect($this->admin->unreadNotifications()->count())->toBe(0);
});

test('shows empty state when no notifications', function () {
    Livewire::actingAs($this->admin)
        ->test(NavbarNotifications::class)
        ->assertSee(__('Tidak ada notifikasi'));
});
