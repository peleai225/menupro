<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementDismissal;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * GET /api/v1/ticker
     *
     * Retourne les annonces actives à afficher dans le bandeau défilant.
     * Accessible sans authentification.
     * Accepte un paramètre optionnel ?restaurant_id= pour filtrer par cible.
     */
    public function ticker(Request $request): JsonResponse
    {
        $restaurant = null;

        if ($request->filled('restaurant_id')) {
            $restaurant = Restaurant::find($request->restaurant_id);
        }

        $query = Announcement::active()->forTicker()->latest();

        $announcements = $query->get()
            ->filter(fn($a) => $restaurant ? $a->isVisibleFor($restaurant) : true)
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'content'      => $a->content,
                'type'         => $a->type,
                'link_url'     => $a->link_url,
                'link_label'   => $a->link_label ?: 'En savoir plus',
                'is_dismissible' => $a->is_dismissible,
            ])
            ->values();

        return response()->json(['data' => $announcements]);
    }

    /**
     * POST /api/v1/ticker/{id}/dismiss
     *
     * Marque une annonce comme fermée pour l'utilisateur authentifié.
     * Requiert auth:sanctum.
     */
    public function dismiss(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        if (!$announcement->is_dismissible) {
            return response()->json(['message' => 'Cette annonce ne peut pas être fermée.'], 422);
        }

        AnnouncementDismissal::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id'         => $request->user()->id,
        ]);

        return response()->json(['message' => 'Annonce masquée.']);
    }
}
