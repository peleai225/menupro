<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\CommandoAgent;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SidebarApiController extends Controller
{
    /**
     * Retourne les compteurs pour la sidebar (badges) et le nombre de notifications non lues.
     * Utilisé par le polling du layout pour rafraîchir sans recharger la page.
     */
    public function badges(Request $request): JsonResponse
    {
        $pendingRestaurants = Restaurant::withoutGlobalScopes()
            ->where('status', RestaurantStatus::PENDING)
            ->count();
        $pendingCommandoAgents = CommandoAgent::pendingReview()->count();
        $unreadNotificationsCount = $request->user()->unreadNotifications()->count();

        return response()->json([
            'pending_restaurants' => $pendingRestaurants,
            'pending_commando_agents' => $pendingCommandoAgents,
            'unread_notifications' => $unreadNotificationsCount,
        ]);
    }
}
