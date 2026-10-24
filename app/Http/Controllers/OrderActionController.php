<?php

namespace App\Http\Controllers;

use App\Enums\OrderRejectionReason;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

/**
 * Page légère « Accepter / Refuser » ouverte depuis la cloche, le push ou le WhatsApp du restaurant
 * (lien signé, valable 30 min : App\Support\OrderActionLink).
 *
 * Choix : la signature suffit, aucune connexion n'est demandée. Sur le téléphone du gérant la
 * session est souvent expirée ; exiger une connexion ferait perdre les minutes que ce lien doit
 * faire gagner (le client attend). Le risque reste limité :
 * - le lien est signé (HMAC APP_KEY), propre à une commande et expire après 30 min ;
 * - il n'ouvre que cette commande et deux actions (accepter / refuser), rien d'autre du compte ;
 * - l'action passe par un POST protégé par le jeton CSRF de la page ET par la signature
 *   (un lien transféré ou prévisualisé ne déclenche rien tout seul) ;
 * - une décision ne passe qu'une fois (Order::accept/reject verrouillent la commande) ;
 * - débit limité (throttle) et chaque décision est journalisée avec l'IP.
 */
class OrderActionController extends Controller
{
    public function show(Request $request, int $order): Response
    {
        if ($denied = $this->rejectInvalidSignature($request)) {
            return $denied;
        }

        $order = $this->findOrder($order);

        return response()->view('pages.order-action', [
            'order'       => $order,
            'actionUrl'   => $request->fullUrl(),
            'prepChoices' => config('orders.acceptance.prep_choices', [10, 15, 20, 30, 45]),
            'defaultPrep' => (int) config('orders.acceptance.default_prep_minutes', 15),
            'reasons'     => OrderRejectionReason::cases(),
        ]);
    }

    public function handle(Request $request, int $order): Response|RedirectResponse
    {
        if ($denied = $this->rejectInvalidSignature($request)) {
            return $denied;
        }

        $order = $this->findOrder($order);

        $data = $request->validate([
            'decision'     => ['required', Rule::in(['accept', 'reject'])],
            'prep_minutes' => ['required_if:decision,accept', 'nullable', 'integer', Rule::in(config('orders.acceptance.prep_choices', [10, 15, 20, 30, 45]))],
            'reason'       => ['required_if:decision,reject', 'nullable', Rule::in(OrderRejectionReason::values())],
            'note'         => ['required_if:reason,other', 'nullable', 'string', 'max:255'],
        ], [
            'reason.required_if' => 'Choisissez le motif du refus.',
            'note.required_if'   => 'Précisez le motif du refus.',
        ]);

        $done = $data['decision'] === 'accept'
            ? $order->accept((int) $data['prep_minutes'])
            : $order->reject(OrderRejectionReason::from($data['reason']), $data['note'] ?? null);

        Log::info('Commande traitée via lien de notification', [
            'order_id' => $order->id,
            'decision' => $data['decision'],
            'applied'  => $done,
            'ip'       => $request->ip(),
        ]);

        $message = match (true) {
            !$done                          => 'Cette commande a déjà été traitée.',
            $data['decision'] === 'accept'  => "Commande acceptée ✅ Le client est prévenu (préparation ~{$order->prep_minutes} min).",
            default                         => 'Commande refusée. Le client est prévenu.',
        };

        // Retour sur la même page signée (même chemin, même signature)
        return redirect()->to($request->fullUrl())->with($done ? 'success' : 'info', $message);
    }

    /**
     * Lien expiré ou modifié : page explicative (403) plutôt qu'une erreur brute.
     */
    private function rejectInvalidSignature(Request $request): ?Response
    {
        if ($request->hasValidSignature()) {
            return null;
        }

        // Signature intacte mais date dépassée = lien expiré ; sinon lien modifié / incomplet
        $expired = URL::hasCorrectSignature($request) && !URL::signatureHasNotExpired($request);

        return response()->view('pages.order-action-invalid', ['expired' => $expired], 403);
    }

    private function findOrder(int $id): Order
    {
        // Pas de portée « restaurant » : la page est utilisable sans connexion
        return Order::withoutGlobalScope('restaurant')
            ->with(['items', 'restaurant'])
            ->findOrFail($id);
    }
}
