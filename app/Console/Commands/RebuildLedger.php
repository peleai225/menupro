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
        {--depuis= : Ne traite que les mouvements à partir de cette date (AAAA-MM-JJ)}';

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
            ->where('amount_paid', '>', 0)
            ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::EXPIRED->value, SubscriptionStatus::CANCELLED->value])
            ->when($since, fn ($q) => $q->where(fn ($d) => $d->where('paid_at', '>=', $since)->orWhere('created_at', '>=', $since)))
            ->orderBy('id')
            ->chunkById(500, function ($subscriptions) use ($ledger) {
                foreach ($subscriptions as $subscription) {
                    if ($ledger->isCollectedSubscription($subscription)) {
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
