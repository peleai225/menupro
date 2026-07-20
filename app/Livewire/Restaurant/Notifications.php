<?php

namespace App\Livewire\Restaurant;

use App\Models\ServiceRequest;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public bool $showDropdown = false;
    public int $lastUnreadCount = 0;
    public int $lastServiceRequestId = 0;
    public int $pendingServiceRequests = 0;

    public function mount(): void
    {
        $this->lastUnreadCount = auth()->user()->unreadNotifications()->count();
        $restaurantId = auth()->user()->restaurant_id;
        $this->lastServiceRequestId = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->max('id') ?? 0;
        $this->pendingServiceRequests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->count();
    }

    /**
     * Called on every poll - check for new notifications and service requests
     */
    public function checkForNewNotifications(): void
    {
        $currentCount = auth()->user()->unreadNotifications()->count();
        if ($currentCount > $this->lastUnreadCount) {
            $this->dispatch('new-notification-arrived');
        }
        $this->lastUnreadCount = $currentCount;

        $restaurantId = auth()->user()->restaurant_id;
        $latestId = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->max('id') ?? 0;
        if ($latestId > $this->lastServiceRequestId && $this->lastServiceRequestId > 0) {
            $latest = ServiceRequest::where('restaurant_id', $restaurantId)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();
            $this->dispatch('new-service-request', [
                'table'      => $latest?->table_number ?? '',
                'type_label' => $latest?->typeLabel() ?? '',
            ]);
        }
        $this->lastServiceRequestId = $latestId;
        $this->pendingServiceRequests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->count();
    }

    public function markServiceRequestDone(int $id): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $req = ServiceRequest::where('restaurant_id', $restaurantId)->where('id', $id)->first();
        if ($req) {
            $req->update(['status' => 'done', 'done_at' => now()]);
        }
        $this->pendingServiceRequests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->count();
        $this->lastServiceRequestId = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')->max('id') ?? 0;
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

        $restaurantId = auth()->user()->restaurant_id;
        $serviceRequests = ServiceRequest::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.restaurant.notifications', [
            'notifications' => $notifications,
            'serviceRequests' => $serviceRequests,
        ]);
    }
}

