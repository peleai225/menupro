<?php

namespace App\Livewire\Crm;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CrmNotificationsBell extends Component
{
    public bool $open = false;
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    #[On('refresh-notifications')]
    public function refresh(): void
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
        unset($this->notifications);
    }

    #[Computed]
    public function notifications()
    {
        return auth()->user()
            ->notifications()
            ->latest()
            ->take(15)
            ->get();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unreadCount = 0;
        unset($this->notifications);
    }

    public function markRead(string $id): void
    {
        $notif = auth()->user()->notifications()->find($id);
        $notif?->markAsRead();
        $this->unreadCount = max(0, $this->unreadCount - 1);
        unset($this->notifications);
    }

    public function toggleOpen(): void
    {
        $this->open = !$this->open;

        if ($this->open) {
            unset($this->notifications);
        }
    }

    public function render()
    {
        return view('livewire.crm.crm-notifications-bell');
    }
}
