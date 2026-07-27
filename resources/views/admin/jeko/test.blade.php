<x-layouts.admin-super title="Test API Jeko">

    <div class="mb-6 flex items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Test API Jeko</h1>
            <p class="text-sm mt-1" style="color:var(--sa-fg-muted);">Vérifie que les credentials configurés fonctionnent.</p>
        </div>
        <a href="{{ route('super-admin.jeko.index') }}" class="btn-secondary text-sm">← Retour KYC</a>
    </div>

    @if(isset($error))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 text-sm">{{ $error }}</div>
    @endif

    <div class="space-y-4">
        @foreach($results as $key => $test)
            <div class="rounded-2xl border p-5 shadow-sm" style="background:var(--sa-card-bg);border-color:var(--sa-border);">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-mono text-sm font-semibold" style="color:var(--sa-fg);">{{ $test['label'] }}</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold
                        {{ $test['ok'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $test['ok'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        HTTP {{ $test['status'] }} — {{ $test['ok'] ? 'OK' : 'ÉCHEC' }}
                    </span>
                </div>
                <pre class="rounded-lg p-3 text-xs overflow-x-auto" style="background:var(--sa-bg);color:var(--sa-fg-muted);">{{ is_array($test['body']) ? json_encode($test['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $test['body'] }}</pre>
            </div>
        @endforeach
    </div>

</x-layouts.admin-super>
