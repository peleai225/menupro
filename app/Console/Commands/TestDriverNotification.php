<?php

namespace App\Console\Commands;

use App\Models\DeliveryDriver;
use App\Services\FcmService;
use Illuminate\Console\Command;

class TestDriverNotification extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'driver:test-notification {driver_id}';

    /**
     * The console command description.
     */
    protected $description = 'Envoie une notification de test à un livreur spécifique';

    /**
     * Execute the console command.
     */
    public function handle(FcmService $fcm): int
    {
        $driverId = $this->argument('driver_id');

        // Récupérer le livreur
        $driver = DeliveryDriver::find($driverId);

        if (!$driver) {
            $this->error("❌ Livreur #{$driverId} introuvable.");
            return Command::FAILURE;
        }

        $this->info("📱 Livreur trouvé : {$driver->full_name}");
        $this->info("📧 Email : {$driver->email}");
        $this->info("📞 Téléphone : {$driver->phone}");

        // Vérifier si FCM est configuré
        if (!$fcm->isConfigured()) {
            $this->error("❌ Firebase n'est pas configuré !");
            $this->line('');
            $this->line('Pour configurer Firebase :');
            $this->line('1. Connectez-vous en tant que super-admin');
            $this->line('2. Allez dans Paramètres → Système');
            $this->line('3. Configurez :');
            $this->line('   - firebase_project_id');
            $this->line('   - firebase_service_account_json');
            return Command::FAILURE;
        }

        $this->info("✅ Firebase configuré");

        // Vérifier si le livreur a un token FCM
        if (!$driver->fcm_token) {
            $this->error("❌ Ce livreur n'a pas de token FCM !");
            $this->line('');
            $this->line("Le livreur doit :");
            $this->line("1. Ouvrir l'app MenuPro Livreur");
            $this->line("2. Se connecter");
            $this->line("3. Accepter les notifications quand demandé");
            $this->line('');
            $this->line("L'app enverra automatiquement son token FCM au backend.");
            return Command::FAILURE;
        }

        $this->info("✅ Token FCM présent : " . substr($driver->fcm_token, 0, 20) . '...');

        // Envoyer la notification de test
        $this->line('');
        $this->info("📤 Envoi de la notification de test...");

        $success = $fcm->sendToToken(
            $driver->fcm_token,
            '🧪 Test MenuPro',
            'Notification de test envoyée avec succès ! Votre configuration fonctionne. 🎉',
            [
                'type' => 'test',
                'timestamp' => now()->toIso8601String(),
            ]
        );

        if ($success) {
            $this->line('');
            $this->info("✅ Notification envoyée avec succès !");
            $this->line('');
            $this->line("📱 Vérifiez sur le téléphone du livreur.");
            $this->line("La notification doit apparaître même si l'app est fermée.");
            return Command::SUCCESS;
        } else {
            $this->error("❌ Échec de l'envoi de la notification.");
            $this->line('');
            $this->line('Vérifiez les logs Laravel :');
            $this->line('tail -f storage/logs/laravel.log | grep FCM');
            return Command::FAILURE;
        }
    }
}
