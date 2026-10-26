<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\PartnerApplication;
use App\Models\User;
use App\Notifications\Admin\NewPartnerApplicationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Page publique « Devenir représentant MenuPro » + formulaire de candidature.
 * Anti-spam : throttle sur la route + champ piège (honeypot) « website ».
 */
class PartnerProgramController extends Controller
{
    public function show(): View
    {
        $cities = DeliveryCity::where('is_active', true)->orderBy('name')->pluck('name');

        return view('pages.public.devenir-representant', [
            'cities' => $cities,
            'rates' => config('crm.representatives'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot : un humain ne voit pas ce champ. Un robot qui le remplit reçoit la
        // même réponse qu'un envoi réussi, mais rien n'est enregistré ni notifié.
        if (filled($request->input('website'))) {
            Log::info('Candidature représentant ignorée (honeypot).');
            return redirect()->route('representative.program')->with('application_sent', true);
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s().-]{8,30}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'legal_status' => ['required', Rule::in(array_keys(PartnerApplication::LEGAL_STATUS_LABELS))],
            'experience' => ['required', 'string', 'min:20', 'max:3000'],
            'availability' => ['required', Rule::in(array_keys(PartnerApplication::AVAILABILITY_LABELS))],
            'motivation' => ['required', 'string', 'min:20', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'accept_terms' => ['accepted'],
        ], [
            'full_name.required' => 'Indiquez votre nom complet.',
            'phone.required' => 'Indiquez votre numéro de téléphone.',
            'phone.regex' => 'Numéro de téléphone invalide.',
            'city.required' => 'Indiquez la ville que vous souhaitez représenter.',
            'legal_status.*' => 'Choisissez votre statut légal.',
            'experience.required' => 'Décrivez votre expérience et votre réseau.',
            'experience.min' => 'Décrivez votre expérience en quelques phrases (20 caractères minimum).',
            'availability.*' => 'Choisissez votre disponibilité.',
            'motivation.required' => 'Expliquez votre motivation.',
            'motivation.min' => 'Expliquez votre motivation en quelques phrases (20 caractères minimum).',
            'attachment.mimes' => 'Pièce jointe : PDF, Word, JPG ou PNG uniquement.',
            'attachment.max' => 'Pièce jointe : 5 Mo maximum.',
            'accept_terms.accepted' => 'Vous devez accepter d\'être recontacté par MenuPro.',
        ]);

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment')) {
            // Disque local (non public) : téléchargeable uniquement depuis le back-office.
            $attachmentPath = $request->file('attachment')->store('partner-applications', 'local');
            $attachmentName = mb_substr($request->file('attachment')->getClientOriginalName(), 0, 200);
        }

        $application = PartnerApplication::create([
            'full_name' => trim($data['full_name']),
            'phone' => trim($data['phone']),
            'email' => $data['email'] ?? null,
            'city' => trim($data['city']),
            'legal_status' => $data['legal_status'],
            'experience' => $data['experience'],
            'availability' => $data['availability'],
            'motivation' => $data['motivation'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status' => PartnerApplication::STATUS_NEW,
        ]);

        $this->notifyAdmins($application);

        return redirect()->route('representative.program')->with('application_sent', true);
    }

    private function notifyAdmins(PartnerApplication $application): void
    {
        try {
            User::where('role', UserRole::SUPER_ADMIN)
                ->where('is_active', true)
                ->get()
                ->filter(fn (User $admin) => $admin->canAdminSection('crm'))
                ->each(fn (User $admin) => $admin->notify(new NewPartnerApplicationNotification($application)));
        } catch (\Throwable $e) {
            // La candidature est enregistrée : une panne de notification ne doit pas la perdre.
            Log::warning('Candidature représentant : notification admin impossible', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
