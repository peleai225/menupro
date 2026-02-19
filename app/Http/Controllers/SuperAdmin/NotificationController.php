<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Liste des notifications (non lues en priorité, puis les 20 dernières).
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->limit(30)
            ->get()
            ->map(function ($n) {
                $data = $n->data;
                $url = $data['url'] ?? '#';
                $message = $data['message'] ?? $data['type'] ?? 'Notification';
                return [
                    'id' => $n->id,
                    'message' => $message,
                    'url' => $url,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Marquer toutes les notifications comme lues (ou une seule si id fourni).
     */
    public function markAsRead(Request $request): JsonResponse
    {
        if ($request->filled('id')) {
            $request->user()
                ->unreadNotifications()
                ->where('id', $request->id)
                ->update(['read_at' => now()]);
        } else {
            $request->user()->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
