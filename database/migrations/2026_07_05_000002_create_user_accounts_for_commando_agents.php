<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $agents = DB::table('commando_agents')->whereNull('user_id')->get();

        foreach ($agents as $agent) {
            // Éviter les doublons si le numéro est déjà dans users
            $email = $agent->whatsapp . '@ambassadeur.menupro.ci';
            $existingUser = DB::table('users')->where('email', $email)->first();

            if ($existingUser) {
                // Lier l'agent existant à l'utilisateur trouvé
                DB::table('commando_agents')
                    ->where('id', $agent->id)
                    ->update(['user_id' => $existingUser->id]);
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => trim($agent->first_name . ' ' . $agent->last_name),
                'email'      => $email,
                'phone'      => $agent->whatsapp,
                'password'   => $agent->password ?? bcrypt(Str::random(16)),
                'role'       => 'commercial',
                'is_active'  => ($agent->status_verification === 'valide') ? 1 : 0,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ]);

            DB::table('commando_agents')
                ->where('id', $agent->id)
                ->update(['user_id' => $userId]);

            // Vérifier qu'un CommercialProfile n'existe pas déjà
            $profileExists = DB::table('crm_commercial_profiles')
                ->where('user_id', $userId)
                ->exists();

            if (!$profileExists) {
                DB::table('crm_commercial_profiles')->insert([
                    'user_id'             => $userId,
                    'uuid'                => (string) Str::uuid(),
                    'city'                => $agent->city,
                    'verification_status' => ($agent->status_verification === 'valide') ? 'valide' : 'pending_review',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Non-réversible : on ne peut pas distinguer quels Users ont été créés par cette migration.
    }
};
