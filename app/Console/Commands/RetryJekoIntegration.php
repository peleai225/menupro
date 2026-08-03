<?php

namespace App\Console\Commands;

use App\Enums\JekoSubMerchantStatus;
use App\Jobs\IntegrateJekoSubMerchantJob;
use App\Models\JekoSubMerchant;
use Illuminate\Console\Command;

class RetryJekoIntegration extends Command
{
    protected $signature = 'jeko:retry-integration {--id= : ID spécifique du sub-merchant à réintégrer}';

    protected $description = 'Relance l\'intégration Jeko pour les restaurants approuvés mais pas encore intégrés';

    public function handle(): int
    {
        if ($this->option('id')) {
            $subMerchant = JekoSubMerchant::find($this->option('id'));

            if (!$subMerchant) {
                $this->error("Sub-merchant #{$this->option('id')} introuvable.");
                return self::FAILURE;
            }

            return $this->integrateOne($subMerchant);
        }

        // Tous les sub-merchants approuvés mais pas intégrés
        $subMerchants = JekoSubMerchant::where('status', JekoSubMerchantStatus::APPROVED)
            ->with('restaurant')
            ->get();

        if ($subMerchants->isEmpty()) {
            $this->info('✓ Aucun restaurant en attente d\'intégration.');
            return self::SUCCESS;
        }

        $this->info("Trouvé {$subMerchants->count()} restaurant(s) à intégrer...\n");

        $success = 0;
        $failed = 0;

        foreach ($subMerchants as $subMerchant) {
            $result = $this->integrateOne($subMerchant);

            if ($result === self::SUCCESS) {
                $success++;
            } else {
                $failed++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("✓ Terminé : {$success} intégré(s), {$failed} échoué(s)");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function integrateOne(JekoSubMerchant $subMerchant): int
    {
        $restaurant = $subMerchant->restaurant;

        $this->line("Restaurant : <info>{$restaurant->name}</info> (ID {$subMerchant->id})");
        $this->line("Téléphone  : <comment>{$subMerchant->mobile_money}</comment>");

        // Normaliser le téléphone avec la nouvelle méthode
        $normalizedPhone = $this->normalizePhone($subMerchant->mobile_money);

        if ($normalizedPhone !== $subMerchant->mobile_money) {
            $this->line("Normalisé  : <comment>{$normalizedPhone}</comment>");
            $subMerchant->update(['mobile_money' => $normalizedPhone]);
        }

        try {
            $this->line('Intégration en cours...');
            IntegrateJekoSubMerchantJob::dispatchSync($subMerchant->fresh());

            $subMerchant->refresh();

            if ($subMerchant->status === JekoSubMerchantStatus::INTEGRATED) {
                $this->info("✓ Intégré avec succès (merchant_id: {$subMerchant->jeko_merchant_id})");
                return self::SUCCESS;
            } else {
                $this->error("✗ Échec : statut toujours {$subMerchant->status->value}");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("✗ Erreur : {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // Déjà avec indicatif 225 et 10 chiffres (format correct)
        if (str_starts_with($digits, '225') && strlen($digits) === 13) {
            return '+' . $digits;
        }

        // Indicatif 225 mais seulement 9 chiffres → ajouter 0
        if (str_starts_with($digits, '225') && strlen($digits) === 12) {
            return '+225' . '0' . substr($digits, 3);
        }

        // Numéro local avec 0 initial (10 chiffres)
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+225' . $digits;
        }

        // Numéro local sans 0 (9 chiffres) → ajouter 0
        if (strlen($digits) === 9) {
            return '+225' . '0' . $digits;
        }

        // Numéro local avec 0 déjà présent (10 chiffres)
        if (strlen($digits) === 10) {
            return '+225' . $digits;
        }

        // Fallback : ajouter +225 tel quel
        return '+225' . $digits;
    }
}
