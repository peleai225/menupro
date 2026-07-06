<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $settings = [
            [
                'key'         => 'elevenlabs_api_key',
                'value'       => 'sk_4d8ccd5869d86b58d0671a064e3608bbb39b21f765a924b4',
                'type'        => 'string',
                'description' => 'Clé API ElevenLabs (synthèse vocale KDS)',
            ],
            [
                'key'         => 'elevenlabs_voice_id',
                'value'       => 'pNInz6obpgDQGcFmaJgB',
                'type'        => 'string',
                'description' => 'ID voix ElevenLabs — remplacer avec une voix africaine choisie',
            ],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $s['key']],
                array_merge($s, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->whereIn('key', ['elevenlabs_api_key', 'elevenlabs_voice_id'])
            ->delete();
    }
};
