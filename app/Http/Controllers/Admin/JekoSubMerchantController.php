<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JekoSubMerchantStatus;
use App\Http\Controllers\Controller;
use App\Jobs\IntegrateJekoSubMerchantJob;
use App\Models\JekoSubMerchant;
use App\Notifications\JekoIntegrationApprovedNotification;
use App\Notifications\JekoIntegrationRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JekoSubMerchantController extends Controller
{
    public function index(Request $request): View
    {
        $query = JekoSubMerchant::with(['restaurant', 'restaurant.owner', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('legal_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $subMerchants = $query->paginate(20)->withQueryString();

        $stats = [
            'total'      => JekoSubMerchant::count(),
            'pending'    => JekoSubMerchant::where('status', JekoSubMerchantStatus::PENDING)->count(),
            'approved'   => JekoSubMerchant::where('status', JekoSubMerchantStatus::APPROVED)->count(),
            'integrated' => JekoSubMerchant::where('status', JekoSubMerchantStatus::INTEGRATED)->count(),
            'rejected'   => JekoSubMerchant::where('status', JekoSubMerchantStatus::REJECTED)->count(),
        ];

        return view('admin.jeko.pending-requests', compact('subMerchants', 'stats'));
    }

    public function show(JekoSubMerchant $jekoSubMerchant): View
    {
        $jekoSubMerchant->load(['restaurant', 'restaurant.owner', 'approver']);

        return view('admin.jeko.show', compact('jekoSubMerchant'));
    }

    public function approve(Request $request, JekoSubMerchant $jekoSubMerchant): RedirectResponse
    {
        if (!$jekoSubMerchant->isPending()) {
            return back()->with('error', 'Cette demande ne peut pas être approuvée (statut actuel : ' . $jekoSubMerchant->status->label() . ').');
        }

        $jekoSubMerchant->update([
            'status'      => JekoSubMerchantStatus::APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        IntegrateJekoSubMerchantJob::dispatch($jekoSubMerchant);

        $restaurant = $jekoSubMerchant->restaurant;
        if ($restaurant?->owner) {
            $restaurant->owner->notify(new JekoIntegrationApprovedNotification($restaurant));
        }

        return back()->with('success', 'Demande approuvée. L\'intégration Jeko est en cours.');
    }

    public function reject(Request $request, JekoSubMerchant $jekoSubMerchant): RedirectResponse
    {
        $request->validate([
            'rejected_reason' => ['required', 'string', 'max:1000'],
        ]);

        if (!$jekoSubMerchant->isPending()) {
            return back()->with('error', 'Cette demande ne peut pas être rejetée (statut actuel : ' . $jekoSubMerchant->status->label() . ').');
        }

        $jekoSubMerchant->update([
            'status'          => JekoSubMerchantStatus::REJECTED,
            'rejected_reason' => $request->input('rejected_reason'),
        ]);

        $restaurant = $jekoSubMerchant->restaurant;
        if ($restaurant?->owner) {
            $restaurant->owner->notify(new JekoIntegrationRejectedNotification($restaurant, $request->input('rejected_reason')));
        }

        return back()->with('success', 'Demande rejetée et restaurant notifié.');
    }
}
