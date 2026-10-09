<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavbarNotifications extends Component
{
    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()?->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function openNotification(string $notificationId): void
    {
        $notification = auth()->user()?->notifications()->find($notificationId);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $taskId = $notification->data['task_id'] ?? null;

        if ($taskId) {
            $this->redirect(route('tasks.show', $taskId), navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        $user = auth()->user();

        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        $notifications = $user ? $user->notifications()->take(10)->get() : collect();

        return view('livewire.navbar-notifications', [
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
