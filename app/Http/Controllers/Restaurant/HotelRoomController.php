<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\HotelRoom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HotelRoomController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;
        $rooms = HotelRoom::where('restaurant_id', $restaurant->id)->ordered()->get();

        return view('pages.restaurant.hotel-rooms', compact('restaurant', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $restaurant = $request->user()->restaurant;

        $maxSort = HotelRoom::where('restaurant_id', $restaurant->id)->max('sort_order') ?? 0;

        $room = HotelRoom::create([
            'restaurant_id' => $restaurant->id,
            'name'          => $request->name,
            'sort_order'    => $maxSort + 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'room' => $room]);
        }

        return back()->with('success', 'Chambre ajoutée.');
    }

    public function update(Request $request, HotelRoom $room)
    {
        $this->authorizeRoom($room, $request);
        $request->validate(['name' => 'required|string|max:100']);

        $room->update(['name' => $request->name]);

        return back()->with('success', 'Chambre modifiée.');
    }

    public function destroy(Request $request, HotelRoom $room)
    {
        $this->authorizeRoom($room, $request);
        $room->delete();

        return back()->with('success', 'Chambre supprimée.');
    }

    public function downloadPdf(Request $request)
    {
        $restaurant = $request->user()->restaurant;
        $rooms = HotelRoom::where('restaurant_id', $restaurant->id)->ordered()->get();

        if ($rooms->isEmpty()) {
            return back()->with('error', 'Aucune chambre configurée.');
        }

        $baseUrl = route('r.menu', ['slug' => $restaurant->slug]);

        $roomsData = $rooms->map(function ($room) use ($baseUrl) {
            $url   = $baseUrl . '?table=' . urlencode($room->name);
            $qrSvg = QrCode::format('svg')->size(280)->margin(1)->errorCorrection('H')->generate($url);

            return [
                'name'      => $room->name,
                'url'       => $url,
                'qr_base64' => base64_encode((string) $qrSvg),
            ];
        })->all();

        $logoPath   = public_path('images/logo-menupro.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('pdf.hotel-room-qrcodes', [
            'restaurant' => $restaurant,
            'rooms'      => $roomsData,
            'logoBase64' => $logoBase64,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isRemoteEnabled', true);

        $filename = 'qrcodes-chambres-' . \Illuminate\Support\Str::slug($restaurant->name) . '.pdf';

        return $pdf->download($filename);
    }

    private function authorizeRoom(HotelRoom $room, Request $request): void
    {
        if ((int) $room->restaurant_id !== (int) $request->user()->restaurant->id) {
            abort(403);
        }
    }
}
