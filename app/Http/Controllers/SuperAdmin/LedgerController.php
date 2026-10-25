<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\Restaurant;
use App\Models\SystemSetting;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Journal des transactions : registre unique de tous les mouvements d'argent de la
 * plateforme (table ledger_entries), avec totaux, filtres, détail et export CSV.
 */
class LedgerController extends Controller
{
    public function index(Request $request, LedgerService $ledger): View
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);

        $entries = (clone $query)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('pages.super-admin.ledger.index', [
            'entries' => $entries,
            'filters' => $filters,
            'totals' => $this->totals($query),
            'byType' => $this->byType($query),
            'restaurants' => Restaurant::withTrashed()->orderBy('name')->pluck('name', 'id'),
            'fees' => [
                'collection' => $ledger->collectionFeePercent(),
                'payout' => $ledger->payoutFeePercent(),
            ],
        ]);
    }

    public function show(LedgerEntry $entry): View
    {
        // Chaîne liée : toutes les lignes de la même commande / du même abonnement
        $chain = LedgerEntry::query()
            ->where(function ($q) use ($entry) {
                $q->where('id', $entry->id);
                if ($entry->order_id) {
                    $q->orWhere('order_id', $entry->order_id);
                }
                if ($entry->subscription_id) {
                    $q->orWhere('subscription_id', $entry->subscription_id);
                }
            })
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        return view('pages.super-admin.ledger.show', [
            'entry' => $entry,
            'chain' => $chain,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);
        $filename = 'journal-transactions_' . $filters['from']->format('Y-m-d') . '_' . $filters['to']->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 : Excel affiche correctement les accents
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'N° ligne', 'Date', 'Type', 'Statut', 'Payé par', 'Téléphone payeur', 'Bénéficiaire',
                'Restaurant', 'Formule', 'Canal', 'Enregistré via', 'Moyen', 'Référence passerelle',
                'Référence secondaire', 'N° commande', 'Montant brut', 'Frais Jeko / passerelle',
                'Taux frais (%)', 'Commission MenuPro', 'Part restaurant', 'Part livreur', 'Net MenuPro',
                'Description',
            ], ';');

            foreach ((clone $query)->orderBy('occurred_at')->orderBy('id')->lazy(1000) as $e) {
                fputcsv($out, [
                    $e->id,
                    $e->occurred_at?->format('d/m/Y H:i'),
                    $e->type_label,
                    $e->status_label,
                    $e->payer_name,
                    $e->payer_phone,
                    $e->beneficiary_name,
                    $e->restaurant_name,
                    $e->restaurant_plan_label,
                    $e->channel_label,
                    $e->via_label,
                    $e->method_label,
                    $e->gateway_reference,
                    $e->gateway_reference_alt,
                    $e->order_reference,
                    $e->gross_amount,
                    $e->gateway_fee,
                    str_replace('.', ',', (string) $e->gateway_fee_rate),
                    $e->menupro_commission,
                    $e->restaurant_share,
                    $e->driver_share,
                    $e->net_menupro,
                    $e->description,
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateFees(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'collection_percent' => 'required|numeric|min:0|max:20',
            'payout_percent' => 'required|numeric|min:0|max:20',
        ]);

        SystemSetting::set(
            LedgerService::SETTING_COLLECTION_FEE,
            (string) round((float) $data['collection_percent'], 2),
            'string',
            'Frais Jeko (%) appliqués à chaque encaissement Jeko (commande ou abonnement)'
        );
        SystemSetting::set(
            LedgerService::SETTING_PAYOUT_FEE,
            (string) round((float) $data['payout_percent'], 2),
            'string',
            'Frais Jeko (%) appliqués à chaque sortie Jeko (reversement restaurant, remboursement)'
        );

        return back()->with('success', 'Taux de frais Jeko enregistrés. Ils s\'appliquent aux nouvelles lignes du journal.');
    }

    // =========================================================================
    // FILTRES ET TOTAUX
    // =========================================================================

    private function filters(Request $request): array
    {
        $parse = function (?string $value, Carbon $default): Carbon {
            if (!$value) {
                return $default;
            }
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return $default;
            }
        };

        $from = $parse($request->get('du'), now()->startOfMonth())->startOfDay();
        $to = $parse($request->get('au'), now())->endOfDay();

        return [
            'from' => $from,
            'to' => $to,
            'type' => (string) $request->get('type', ''),
            'restaurant' => $request->integer('restaurant') ?: null,
            'method' => (string) $request->get('moyen', ''),
            'status' => (string) $request->get('statut', ''),
            'q' => trim((string) $request->get('q', '')),
            'demo' => $request->boolean('demo'),
        ];
    }

    private function filteredQuery(array $f): Builder
    {
        return LedgerEntry::query()
            ->whereBetween('occurred_at', [$f['from'], $f['to']])
            ->when(!$f['demo'], fn ($q) => $q->where('is_demo', false))
            ->when($f['type'] === LedgerEntry::FILTER_JEKO_FEES, fn ($q) => $q->where('gateway_fee', '>', 0)->where('method', 'jeko'))
            ->when($f['type'] === 'orders', fn ($q) => $q->whereIn('type', LedgerEntry::ORDER_TYPES))
            ->when($f['type'] === 'subscriptions', fn ($q) => $q->whereIn('type', LedgerEntry::SUBSCRIPTION_TYPES))
            ->when(isset(LedgerEntry::TYPE_LABELS[$f['type']]), fn ($q) => $q->where('type', $f['type']))
            ->when($f['restaurant'], fn ($q) => $q->where('restaurant_id', $f['restaurant']))
            ->when(isset(LedgerEntry::METHOD_LABELS[$f['method']]), fn ($q) => $q->where('method', $f['method']))
            ->when(isset(LedgerEntry::STATUS_LABELS[$f['status']]), fn ($q) => $q->where('status', $f['status']))
            ->when($f['q'] !== '', function ($q) use ($f) {
                $term = $f['q'];
                $digits = preg_replace('/\D+/', '', $term);
                $q->where(function ($s) use ($term, $digits) {
                    $s->where('gateway_reference', $term)
                        ->orWhere('gateway_reference_alt', $term)
                        ->orWhere('order_reference', $term)
                        ->orWhere('gateway_reference', 'like', "%{$term}%")
                        ->orWhere('gateway_reference_alt', 'like', "%{$term}%")
                        ->orWhere('order_reference', 'like', "%{$term}%")
                        ->orWhere('payer_name', 'like', "%{$term}%");
                    if (strlen($digits) >= 6) {
                        // Téléphone : on compare les 8 derniers chiffres (indicatif et espaces variables)
                        $s->orWhereRaw("REPLACE(REPLACE(REPLACE(payer_phone, ' ', ''), '-', ''), '.', '') like ?", ['%' . substr($digits, -8)]);
                    }
                    if (ctype_digit($term)) {
                        $s->orWhere('order_id', (int) $term)->orWhere('id', (int) $term);
                    }
                });
            });
    }

    private function totals(Builder $query): array
    {
        $settled = "'" . implode("','", LedgerEntry::SETTLED_STATUSES) . "'";
        $orderTypes = "'" . implode("','", LedgerEntry::ORDER_TYPES) . "'";
        $subTypes = "'" . implode("','", LedgerEntry::SUBSCRIPTION_TYPES) . "'";
        $payout = LedgerEntry::TYPE_PAYOUT_RESTAURANT;
        $succeeded = LedgerEntry::STATUS_SUCCEEDED;

        $row = (clone $query)->toBase()->selectRaw("
            COUNT(*) as lines_count,
            SUM(CASE WHEN direction = 'in' AND status IN ({$settled}) AND (type IN ({$orderTypes}) OR type IN ({$subTypes})) THEN gross_amount ELSE 0 END) as collected,
            SUM(CASE WHEN direction = 'in' AND status IN ({$settled}) AND type IN ({$orderTypes}) AND method <> 'cash' THEN gross_amount ELSE 0 END) as collected_online,
            SUM(CASE WHEN direction = 'in' AND status IN ({$settled}) AND type IN ({$orderTypes}) AND method = 'cash' THEN gross_amount ELSE 0 END) as collected_cash,
            SUM(CASE WHEN status IN ({$settled}) AND type IN ({$subTypes}) THEN gross_amount ELSE 0 END) as subscriptions,
            SUM(CASE WHEN status IN ({$settled}) AND direction = 'in' THEN gateway_fee ELSE 0 END) as fees_collection,
            SUM(CASE WHEN status IN ({$settled}) AND direction = 'out' THEN gateway_fee ELSE 0 END) as fees_payout,
            SUM(CASE WHEN status IN ({$settled}) AND type NOT IN ({$subTypes}) THEN menupro_commission ELSE 0 END) as commissions,
            SUM(CASE WHEN type = '{$payout}' AND status = '{$succeeded}' THEN gross_amount ELSE 0 END) as paid_out,
            SUM(CASE WHEN type IN ({$orderTypes}) AND status = '{$succeeded}' AND method IN ('jeko', 'wave') THEN restaurant_share ELSE 0 END) as to_pay_out,
            SUM(CASE WHEN type = 'refund_customer' AND status IN ({$settled}) THEN gross_amount ELSE 0 END) as refunded,
            SUM(CASE WHEN status IN ({$settled}) THEN net_menupro ELSE 0 END) as net
        ")->first();

        $totals = array_map(fn ($v) => (int) ($v ?? 0), (array) $row);
        $totals['fees'] = $totals['fees_collection'] + $totals['fees_payout'];

        return $totals;
    }

    private function byType(Builder $query): array
    {
        $settled = "'" . implode("','", LedgerEntry::SETTLED_STATUSES) . "'";

        return (clone $query)->toBase()
            ->select('type')
            ->selectRaw('COUNT(*) as lines_count')
            ->selectRaw("SUM(CASE WHEN status IN ({$settled}) THEN gross_amount ELSE 0 END) as gross")
            ->selectRaw("SUM(CASE WHEN status IN ({$settled}) THEN gateway_fee ELSE 0 END) as fees")
            ->selectRaw("SUM(CASE WHEN status IN ({$settled}) THEN net_menupro ELSE 0 END) as net")
            ->groupBy('type')
            ->orderByDesc(DB::raw('gross'))
            ->get()
            ->map(fn ($r) => [
                'type' => $r->type,
                'label' => LedgerEntry::TYPE_LABELS[$r->type] ?? $r->type,
                'count' => (int) $r->lines_count,
                'gross' => (int) $r->gross,
                'fees' => (int) $r->fees,
                'net' => (int) $r->net,
            ])
            ->all();
    }
}
