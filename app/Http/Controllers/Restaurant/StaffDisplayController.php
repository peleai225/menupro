<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffDisplayController extends Controller
{
    public function generateToken(Request $request): JsonResponse
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->staff_token = Str::random(48);
        $restaurant->save();

        return response()->json([
            'token' => $restaurant->staff_token,
            'url'   => route('staff.display', $restaurant->staff_token),
        ]);
    }

    public function display(string $token): View
    {
        $restaurant = Restaurant::where('staff_token', $token)->firstOrFail();
        return view('pages.staff.display', compact('restaurant', 'token'));
    }

    public function data(string $token): JsonResponse
    {
        $restaurant = Restaurant::where('staff_token', $token)->firstOrFail();

        $requests = ServiceRequest::where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($r) => [
                'id'         => $r->id,
                'table'      => $r->table_number,
                'type'       => $r->type,
                'type_label' => $r->typeLabel(),
                'type_icon'  => $r->typeIcon(),
                'notes'      => $r->notes,
                'age'        => $r->created_at->diffForHumans(['locale' => 'fr']),
            ]);

        return response()->json(['requests' => $requests]);
    }

    public function markDone(string $token, int $id): JsonResponse
    {
        $restaurant = Restaurant::where('staff_token', $token)->firstOrFail();
        $req = ServiceRequest::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $req->update(['status' => 'done', 'done_at' => now()]);

        return response()->json(['success' => true]);
    }
}
