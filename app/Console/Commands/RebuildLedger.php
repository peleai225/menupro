<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\PayoutTransaction;
use App\Models\Subscription;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Reconstitue le journal des transactions à partir des données existantes
 * (commandes, abonnements, remboursements, retraits de wallet, gains livreurs).
 *
 * Idempotent : une ligne déjà présente (même clé source:id:nature) n'est ni dupliquée
 * ni modifiée. Peut donc être relancée sans risque après chaque déploiement.
 */
class RebuildLedger extends Command
{
    protected $signature = 'ledger:reconstruire
        {--dry-run : Affiche ce qui serait écrit sans rien écrire}
        {--depuis= : Ne traite que les mouvements à partir de cette date (AAAA-MM-JJ)}
        {--recalculer-frais : Recalcule frais Jeko et net MenuPro des lignes EXISTANTES avec les taux actuels (ne crée rien)}';

    protected $description = 'Reconstitue le journal des transactions (ledger_entries) depuis l\'historique existant';

    /** @var array<string, array{created:int, existing:int}> */
    private array $report = [];

    private bool $dryRun = false;

    public function handle(LedgerService $ledger): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $since = null;

        if ($this->option('depuis')) {
            try {
                $since = Carbon::parse($this->option('depuis'))->startOfDay();
            } catch (\Throwable) {
                $this->error('Date --depuis invalide (format attendu : AAAA-MM-JJ).');

                return self::FAILURE;
            }
        }

        if ($this->option('recalculer-frais')) {
            return $this->recalculateFees($ledger, $since);
        }

        $this->info(($this->dryRun ? '[SIMULATION] ' : '') . 'Reconstitution du journal des transactions'
            . ($since ? ' depuis le ' . $since->format('d/m/Y') : ''));

        // ─── Commandes : encaissements, échecs, reversements ─────────────────
        Order::withoutGlobalScope('restaurant')
            ->where(function ($q) {
                $q->whereIn('payment_status', [PaymentStatus::COMPLETED->value, PaymentStatus::REFUNDED->value, PaymentStatus::FAILED->value])
                    ->orWhere(function ($q) {
                        $q->where('status', OrderStatus::COMPLETED->value)
                            ->where(fn ($m) => $m->whereNull('payment_method')->orWhereIn('payment_method', LedgerService::CASH_METHODS));
                    })
                    ->orWhereNotNull('payout_status');
            })
            ->when($since, fn ($q) => $q->where(fn ($d) => $d->where('paid_at', '>=', $since)
                ->orWhere('payout_at', '>=', $since)
                ->orWhere('updated_at', '>=', $since)))
            ->orderBy('id')
            ->chunkById(500, function ($orders) use ($ledger) {
                foreach ($orders as $order) {
                    $isCash = $ledger->normalizeMethod($order->payment_method) === 'cash';
                    $paid = in_array($order->payment_status, [PaymentStatus::COMPLETED, PaymentStatus::REFUNDED], true)
                        || ($isCash && $order->status === OrderStatus::COMPLETED);

                    if ($paid) {
                        $this->put('Encaissements de commandes', $ledger, $ledger->orderPaymentEntry($order, 'reconstruction', reconstruct: true));
                    } elseif ($order->payment_status === PaymentStatus::FAILED) {
                        $this->put('Paiements échoués', $ledger, $ledger->orderPaymentFailedEntry($order, 'reconstruction'));
                    }

                    if (!$isCash && $paid) {
                        if (in_array($order->payout_status, LedgerService::PAYOUT_DONE, true)) {
                            $this->put('Reversements (commandes)', $ledger, $ledger->orderPayoutEntry($order, 'reconstruction'));
                        } elseif ($order->payout_status === 'failed') {
                            $this->put('Reversements échoués', $ledger, $ledger->orderPayoutFailedEntry($order, 'reconstruction'));
                        }
                    }
                }
                $ledger->flushCache();
            });

        // ─── Remboursements ──────────────────────────────────────────────────
        OrderRefund::query()
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->orderBy('id')
            ->chunkById(500, function ($refunds) use ($ledger) {
                foreach ($refunds as $refund) {
                    $this->put('Remboursements', $ledger, $ledger->refundEntry($refund, 'reconstruction'));
                }
            });

        // ─── Abonnements encaissés ou échoués ────────────────────────────────
        Subscription::withoutGlobalScope('restaurant')
            ->with('plan:id,name,slug')
            // Payants, ou prolongations manuelles offertes (0 F, marquées « offert »)
            ->where(fn ($q) => $q->where('amount_paid', '>', 0)->orWhere('payment_method', 'admin_manual'))
            ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::EXPIRED->value, SubscriptionStatus::CANCELLED->value])
            ->when($since, fn ($q) => $q->where(fn ($d) => $d->where('paid_at', '>=', $since)->orWhere('created_at', '>=', $since)))
            ->orderBy('id')
            ->chunkById(500, function ($subscriptions) use ($ledger) {
                foreach ($subscriptions as $subscription) {
                    if ($ledger->isCollectedSubscription($subscription) || $ledger->isOfferedManualSubscription($subscription)) {
                        $this->put('Abonnements', $ledger, $ledger->subscriptionEntry($subscription, 'reconstruction'));
                    }
                    // Les abonnements annulés ne sont pas reconstitués comme « échoués » :
                    // l'historique ne permet pas de distinguer un échec de paiement d'une
                    // annulation volontaire.
                }
            });

        // ─── Retraits de wallet ──────────────────────────────────────────────
        PayoutTransaction::query()
            ->when($since, fn ($q) => $q->where('updated_at', '>=', $since))
            ->orderBy('id')
            ->chunkById(500, function ($payouts) use ($ledger) {
                foreach ($payouts as $payout) {
                    $this->put('Retraits de wallet', $ledger, $ledger->payoutTransactionEntry($payout, 'reconstruction'));
                }
            });

        // ─── Commissions livreurs ────────────────────────────────────────────
        DriverEarning::query()
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->orderBy('id')
            ->chunkById(500, function ($earnings) use ($ledger) {
                foreach ($earnings as $earning) {
                    $this->put('Commissions livreurs', $ledger, $ledger->driverEarningEntry($earning, 'reconstruction'));
                }
            });

        $rows = [];
        $totalCreated = 0;
        foreach ($this->report as $label => $counts) {
            $rows[] = [$label, $counts['created'], $counts['existing']];
            $totalCreated += $counts['created'];
        }

        $this->table([
            'Mouvements',
            $this->dryRun ? 'À créer' : 'Créés',
            'Déjà présents',
        ], $rows ?: [['—', 0, 0]]);

        $this->info($this->dryRun
            ? "Simulation terminée : {$totalCreated} ligne(s) seraient créées. Rien n'a été écrit."
            : "Terminé : {$totalCreated} ligne(s) créée(s).");

        return self::SUCCESS;
    }

    /**
     * Recalcule gateway_fee / gateway_fee_rate / net_menupro des lignes Jeko existantes avec
     * les taux ACTUELS (réglages du journal), sans toucher aux autres champs ni créer de ligne.
     * Net recalculé = net actuel + ancien frais − nouveau frais (les autres parts sont figées).
     *  - encaissements Jeko (commandes, abonnements) : taux d'encaissement (0 si l'abonnement
     *    a été saisi à la main : dépôt hors plateforme) ;
     *  - reversements de commandes et remboursements Jeko : taux de reversement ;
     *  - retraits de wallet Jeko : seulement si le frais venait du taux (fee Jeko non renvoyé) ;
     *  - lignes échouées, espèces, Wave, mouvements internes : inchangées.
     */
    private function recalculateFees(LedgerService $ledger, ?Carbon $since): int
    {
        $collection = $ledger->collectionFeePercent();
        $payout = $ledger->payoutFeePercent();

        $this->info(($this->dryRun ? '[SIMULATION] ' : '') . 'Recalcul des frais Jeko des lignes existantes'
            . " — encaissement {$collection} %, reversement {$payout} %"
            . ($since ? ' depuis le ' . $since->format('d/m/Y') : ''));

        $stats = [];
        $changedTotal = 0;
        $deltaTotal = 0;

        LedgerEntry::query()
            ->where('method', 'jeko')
            ->where('status', '!=', LedgerEntry::STATUS_FAILED)
            ->whereIn('direction', ['in', 'out'])
            ->when($since, fn ($q) => $q->where('occurred_at', '>=', $since))
            ->orderBy('id')
            ->chunkById(500, function ($entries) use ($collection, $payout, $ledger, &$stats, &$changedTotal, &$deltaTotal) {
                foreach ($entries as $entry) {
                    $rate = $this->currentRateFor($entry, $collection, $payout);
                    if ($rate === null) {
                        continue;
                    }

                    $newFee = $ledger->fee((int) $entry->gross_amount, $rate);
                    $oldFee = (int) $entry->gateway_fee;

                    if ($newFee === $oldFee && abs((float) $entry->gateway_fee_rate - $rate) < 0.0001) {
                        continue;
                    }

                    $label = LedgerEntry::TYPE_LABELS[$entry->type] ?? $entry->type;
                    $stats[$label] ??= ['lines' => 0, 'before' => 0, 'after' => 0];
                    $stats[$label]['lines']++;
                    $stats[$label]['before'] += $oldFee;
                    $stats[$label]['after'] += $newFee;
                    $changedTotal++;
                    $deltaTotal += $oldFee - $newFee;

                    if (!$this->dryRun) {
                        // Mise à jour ciblée (pas d'événement, pas de nouvelle ligne)
                        LedgerEntry::whereKey($entry->id)->update([
                            'gateway_fee' => $newFee,
                            'gateway_fee_rate' => $rate,
                            'net_menupro' => (int) $entry->net_menupro + $oldFee - $newFee,
                            'updated_at' => now(),
                        ]);
                    }
                }
            });

        $rows = [];
        foreach ($stats as $label => $row) {
            $rows[] = [$label, $row['lines'], number_format($row['before'], 0, ',', ' '), number_format($row['after'], 0, ',', ' ')];
        }

        $this->table(['Mouvements', 'Lignes modifiées', 'Frais avant (F)', 'Frais après (F)'], $rows ?: [['—', 0, 0, 0]]);

        $delta = number_format($deltaTotal, 0, ',', ' ');
        $this->info($this->dryRun
            ? "Simulation terminée : {$changedTotal} ligne(s) seraient recalculées (net MenuPro {$delta} F). Rien n'a été écrit."
            : "Terminé : {$changedTotal} ligne(s) recalculée(s) (net MenuPro {$delta} F).");

        return self::SUCCESS;
    }

    /** Taux actuel applicable à une ligne Jeko existante, ou null si elle ne doit pas changer. */
    private function currentRateFor(LedgerEntry $entry, float $collection, float $payout): ?float
    {
        if ($entry->direction === 'in') {
            // Abonnement saisi par le super-admin (dépôt Jeko hors plateforme) : jamais de frais
            return $entry->source_type === 'subscription' && $entry->channel === 'back_office' ? 0.0 : $collection;
        }

        return match ($entry->source_type) {
            'order', 'order_refund' => $payout,
            'payout_transaction' => (float) (PayoutTransaction::whereKey($entry->source_id)->value('fee') ?? 0) > 0 ? null : $payout,
            default => null,
        };
    }

    private function put(string $label, LedgerService $ledger, ?array $entry): void
    {
        if (!$entry) {
            return;
        }

        $this->report[$label] ??= ['created' => 0, 'existing' => 0];

        if (LedgerEntry::where('idempotency_key', $entry['idempotency_key'])->exists()) {
            $this->report[$label]['existing']++;

            return;
        }

        if ($this->dryRun) {
            $this->report[$label]['created']++;

            return;
        }

        if ($ledger->write($entry)) {
            $this->report[$label]['created']++;
        }
    }
}
