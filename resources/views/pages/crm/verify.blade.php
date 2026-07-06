@php
    $profile = \App\Models\Crm\CommercialProfile::where('uuid', $uuid)->first();
    $valid = $profile && $profile->isValide();

    if ($profile) {
        // Anti-abus : vérifier le nombre de scans récents pour ce profil
        $maxScansPerHour = config('crm.fraud.max_qr_scans_per_hour', 10);
        $recentScans = \App\Models\Crm\VerifyScan::where('user_id', $profile->user_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentScans > $maxScansPerHour) {
            \Illuminate\Support\Facades\Log::warning(
                "QR scan flood detected for profile {$profile->uuid} from IP " . request()->ip()
            );
            abort(429, 'Trop de vérifications. Réessayez dans une heure.');
        }

        \App\Models\Crm\VerifyScan::create([
            'user_id' => $profile->user_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification Agent - MenuPro CRM</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        @if($valid)
        <div class="text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-full bg-emerald-500/10 border-2 border-emerald-500/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Agent vérifié</h1>
                <p class="text-sm text-gray-400 mt-1">Cet agent est un commercial officiel MenuPro</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 space-y-3">
                <div class="flex items-center gap-3">
                    <img src="{{ $profile->user->avatar_url }}" class="w-12 h-12 rounded-xl object-cover border border-gray-700">
                    <div>
                        <p class="font-semibold text-white">{{ $profile->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $profile->badge_id }} · {{ $profile->city }}</p>
                    </div>
                </div>
            </div>
        </div>
        @elseif($profile)
        <div class="text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-full bg-red-500/10 border-2 border-red-500/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Agent non actif</h1>
                <p class="text-sm text-gray-400 mt-1">Ce compte n'est pas actuellement validé</p>
            </div>
        </div>
        @else
        <div class="text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-full bg-red-500/10 border-2 border-red-500/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">Agent introuvable</h1>
                <p class="text-sm text-gray-400 mt-1">Ce QR code ne correspond à aucun agent MenuPro</p>
            </div>
        </div>
        @endif

        <div class="mt-8 text-center">
            <a href="{{ route('home') }}" class="text-xs text-gray-500 hover:text-gray-300 transition">
                ← Retour à MenuPro
            </a>
        </div>
    </div>
</body>
</html>
