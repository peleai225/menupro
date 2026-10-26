{{-- Onglets du programme « Représentant de ville » --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach([
        ['super-admin.partner-applications.index', 'super-admin.partner-applications.*', 'Candidatures'],
        ['super-admin.representatives.index', 'super-admin.representatives.*', 'Représentants'],
        ['super-admin.qr-campaigns.index', 'super-admin.qr-campaigns.*', 'Campagnes QR'],
    ] as [$routeName, $pattern, $label])
        <a href="{{ route($routeName) }}"
           class="h-9 px-4 inline-flex items-center rounded-xl border text-sm font-medium"
           style="{{ request()->routeIs($pattern) ? 'background:#f97316;border-color:#f97316;color:#fff;' : 'background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if(session('success'))
    <div class="mb-6 p-4 rounded-xl border text-sm" style="background:#f0fdf4;border-color:#86efac;color:#166534;">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-6 p-4 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b;">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
