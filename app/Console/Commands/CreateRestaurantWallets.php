<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Models\RestaurantWallet;
use Illuminate\Console\Command;

class CreateRestaurantWallets extends Command
{
    protected $signature = 'wallets:create
                            {--dry-run : Afficher les actions sans les exécuter}
                            {--restaurant= : ID du restaurant (optionnel, sinon tous)}';

    protected $description = 'Crée les wallets pour les restaurants qui n\'en ont pas';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $restaurantId = $this->option('restaurant');

        $query = Restaurant::query();
        if ($restaurantId) {
            $query->where('id', $restaurantId);
        }

        $restaurants = $query->get();
        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($restaurants as $restaurant) {
            if (RestaurantWallet::where('restaurant_id', $restaurant->id)->exists()) {
                $this->line("  <comment>Skip</comment> {$restaurant->name} (wallet existe)");
                $skipped++;
                continue;
            }

            $phone = preg_replace('/\D/', '', $restaurant->phone ?? '');
            if (empty($phone) || strlen($phone) < 8) {
                $this->line("  <error>Skip</error> {$restaurant->name} (téléphone manquant ou invalide)");
                $errors++;
                continue;
            }

            $prefix = '225';
            if (str_starts_with($phone, '225')) {
                $phone = substr($phone, 3);
            }
            $fullPhone = $prefix . $phone;

            if ($dryRun) {
                $this->line("  <info>Would create</info> wallet pour {$restaurant->name} (phone: {$fullPhone})");
                $created++;
                continue;
            }

            try {
                RestaurantWallet::create([
                    'restaurant_id' => $restaurant->id,
                    'balance' => 0,
                    'phone' => $fullPhone,
                    'prefix' => $prefix,
                ]);
                $this->line("  <info>Créé</info> wallet pour {$restaurant->name}");
                $created++;
            } catch (\Exception $e) {
                $this->line("  <error>Erreur</error> {$restaurant->name}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Résumé: {$created} créé(s), {$skipped} ignoré(s), {$errors} erreur(s)");

        return $errors > 0 ? 1 : 0;
    }
}
