<?php

namespace App\Livewire\Restaurant;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public bool $showDropdown = false;

    public function mount(): void
    {
        //
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function markAsRead(string $notificationId): void
    {
        try {
            $notification = auth()->user()->notifications()->find($notificationId);
            
            if ($notification && !$notification->read_at) {
                $notification->markAsRead();
                $this->dispatch('notification-read');
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function markAllAsRead(): void
    {
        try {
            auth()->user()->unreadNotifications->markAsRead();
            $this->dispatch('notifications-read');
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function delete(string $notificationId): void
    {
        try {
            $notification = auth()->user()->notifications()->find($notificationId);
            if ($notification) {
                $notification->delete();
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications->count();
    }

    public function render()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(10, ['*'], 'notifications_page');

        return view('livewire.restaurant.notifications', [
            'notifications' => $notifications,
        ]);
    }
}

