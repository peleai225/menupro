<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PartnerApplication;
use App\Services\Crm\RepresentativeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Back-office : candidatures au programme « Représentant de ville ».
 */
class PartnerApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));

        $applications = PartnerApplication::query()
            ->when($status && isset(PartnerApplication::STATUS_LABELS[$status]), fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = PartnerApplication::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('pages.super-admin.representatives.applications-index', compact('applications', 'counts', 'status', 'search'));
    }

    public function show(PartnerApplication $application): View
    {
        $application->load(['reviewer', 'user.representativeProfile']);

        return view('pages.super-admin.representatives.application-show', [
            'application' => $application,
            'defaults' => config('crm.representatives'),
        ]);
    }

    public function update(Request $request, PartnerApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(PartnerApplication::STATUS_LABELS))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($application->user_id && $data['status'] !== PartnerApplication::STATUS_ACCEPTED) {
            return back()->withErrors(['status' => 'Le représentant a déjà été créé : la candidature reste « acceptée ».']);
        }

        $application->update([
            'status' => $data['status'],
            'internal_notes' => $data['internal_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Candidature mise à jour.');
    }

    public function attachment(PartnerApplication $application): StreamedResponse
    {
        abort_unless($application->attachment_path && Storage::disk('local')->exists($application->attachment_path), 404);

        return Storage::disk('local')->download(
            $application->attachment_path,
            $application->attachment_name ?: basename($application->attachment_path)
        );
    }

    /**
     * « Créer le représentant » depuis une candidature acceptée.
     */
    public function createRepresentative(Request $request, PartnerApplication $application, RepresentativeService $service): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:80'],
            'subscription_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'trial_ends_at' => ['required', 'date'],
            'quarterly_target' => ['required', 'integer', 'min:0', 'max:10000'],
            'exclusivity_active' => ['nullable', 'boolean'],
        ]);
        $data['exclusivity_active'] = $request->boolean('exclusivity_active');

        $result = $service->createFromApplication($application, $data);

        return redirect()
            ->route('super-admin.representatives.show', $result['profile'])
            ->with('success', "Représentant créé. Identifiant : {$result['user']->email} — mot de passe : {$result['password']} — Communiquez-le lui via WhatsApp.");
    }
}
