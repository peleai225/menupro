<?php

namespace App\Http\Controllers\Commando;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentDashboardController extends Controller
{
    /**
     * Dashboard agent : lien parrainage + accès carte si VALIDE.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $agent = $user->commandoAgent;

        if (!$agent) {
            abort(404, 'Profil agent introuvable.');
        }

        return view('pages.commando.dashboard', compact('agent'));
    }

    /**
     * Affiche la carte agent (badge) - accès uniquement si status VALIDE et non banni.
     */
    public function card(Request $request): View|\Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();
        $agent = $user->commandoAgent;

        if (!$agent || !$agent->canGenerateCard()) {
            abort(403, 'Vous n\'êtes pas autorisé à générer votre carte.');
        }

        $qrSvg = QrCode::size(100)->margin(1)->generate($agent->verify_url);

        return view('pages.commando.agent-card', compact('agent', 'qrSvg'));
    }

    /**
     * Télécharge la carte agent en PDF.
     */
    public function downloadPdf(Request $request)
    {
        $user = $request->user();
        $agent = $user->commandoAgent;

        if (!$agent || !$agent->canGenerateCard()) {
            abort(403, 'Vous n\'êtes pas autorisé à télécharger votre carte.');
        }

        $photoUrl = $agent->photo_url;
        if ($photoUrl && !str_starts_with($photoUrl, 'http')) {
            $photoUrl = url($photoUrl);
        }
        $qrSvg = QrCode::format('svg')->size(120)->margin(1)->generate($agent->verify_url);
        $qrSvgBase64 = base64_encode((string) $qrSvg);

        $pdf = Pdf::loadView('pages.commando.agent-card-pdf', compact('agent', 'photoUrl', 'qrSvgBase64'))
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true);

        $filename = 'carte-agent-' . \Illuminate\Support\Str::slug($agent->full_name) . '.pdf';

        return $pdf->download($filename);
    }
}
