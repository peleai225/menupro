@php
    use App\Models\QrCampaign;
    $card = 'rounded-xl p-5 border shadow-sm';
    $cardStyle = 'background:var(--sa-card);border-color:var(--sa-border);';
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
    $isSignup = $campaign->target_type === QrCampaign::TARGET_RESTAURANT_SIGNUP;
@endphp
<x-layouts.admin-super title="Campagne QR — {{ $campaign->name }}">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('super-admin.qr-campaigns.index') }}" style="color:var(--sa-muted-fg);" aria-label="Retour aux campagnes">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $campaign->name }}</h1>
        @unless($campaign->is_active)<span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#f1f5f9;color:#475569;">Désactivée</span>@endunless
    </div>

    @include('pages.super-admin.representatives._nav')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach([
                    ['Scans au total', number_format($campaign->scans_count, 0, ',', ' ')],
                    ['Mobile', number_format($devices['mobile'] ?? 0, 0, ',', ' ')],
                    ['Ordinateur', number_format($devices['desktop'] ?? 0, 0, ',', ' ')],
                    ['Inscriptions restaurant', $isSignup ? $signupsCount : '—'],
                ] as [$label, $value])
                    <div class="{{ $card }}" style="{{ $cardStyle }}">
                        <p class="text-xs font-medium" style="color:var(--sa-muted-fg);">{{ $label }}</p>
                        <p class="text-2xl font-bold mt-1 tabular-nums" style="color:var(--sa-fg);">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Scans par jour (30 derniers jours) --}}
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <p class="text-sm font-semibold mb-4" style="color:var(--sa-fg);">Scans par jour — 30 derniers jours</p>
                <div class="flex items-end gap-1 h-40" role="img" aria-label="Histogramme des scans par jour">
                    @foreach($days as $d)
                        <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ \Illuminate\Support\Carbon::parse($d['day'])->format('d/m') }} : {{ $d['total'] }} scan(s)">
                            <div class="w-full rounded-t" style="height:{{ $d['total'] > 0 ? max(4, round($d['total'] * 100 / $maxDay)) : 0 }}%;background:#f97316;"></div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-[11px] mt-2" style="color:var(--sa-muted-fg);">
                    <span>{{ \Illuminate\Support\Carbon::parse($days->first()['day'])->format('d/m') }}</span>
                    <span>{{ \Illuminate\Support\Carbon::parse($days->last()['day'])->format('d/m') }}</span>
                </div>
                <table class="min-w-full text-xs mt-4">
                    <tbody>
                        @foreach($days->reverse()->filter(fn ($d) => $d['total'] > 0)->take(10) as $d)
                            <tr class="border-t" style="border-color:var(--sa-border);">
                                <td class="py-1.5" style="color:var(--sa-fg);">{{ \Illuminate\Support\Carbon::parse($d['day'])->translatedFormat('l d F') }}</td>
                                <td class="py-1.5 text-right tabular-nums font-semibold" style="color:var(--sa-fg);">{{ $d['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($isSignup)
                <div class="border rounded-xl shadow-sm overflow-x-auto" style="{{ $cardStyle }}">
                    <p class="px-4 pt-4 text-sm font-semibold" style="color:var(--sa-fg);">Restaurants inscrits depuis ce QR</p>
                    <table class="min-w-full text-sm mt-2">
                        <tbody>
                            @forelse($signups as $r)
                                <tr class="border-t" style="border-color:var(--sa-border);">
                                    <td class="px-4 py-2"><a href="{{ route('super-admin.restaurants.show', $r) }}" class="hover:underline" style="color:var(--sa-fg);">{{ $r->name }}</a></td>
                                    <td class="px-4 py-2" style="color:var(--sa-muted-fg);">{{ $r->city }}</td>
                                    <td class="px-4 py-2 text-xs" style="color:var(--sa-fg);">{{ $r->status?->label() }}</td>
                                    <td class="px-4 py-2 text-xs" style="color:var(--sa-muted-fg);">{{ $r->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-6 text-center" style="color:var(--sa-muted-fg);">Aucune inscription pour l'instant.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="{{ $card }} text-center" style="{{ $cardStyle }}">
                <div class="mx-auto bg-white rounded-lg p-2 inline-block [&>svg]:w-56 [&>svg]:h-56">{!! $svgPreview !!}</div>
                <p class="font-mono text-sm mt-3 break-all" style="color:var(--sa-fg);">{{ $campaign->short_url }}</p>
                <p class="text-xs mt-1 break-all" style="color:var(--sa-muted-fg);">→ {{ $campaign->destinationUrl() }}</p>
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <a href="{{ route('super-admin.qr-campaigns.png', $campaign) }}" class="h-10 inline-flex items-center justify-center rounded-xl text-sm font-medium text-white" style="background:#f97316;">PNG HD</a>
                    <a href="{{ route('super-admin.qr-campaigns.svg', $campaign) }}" class="h-10 inline-flex items-center justify-center rounded-xl border text-sm font-medium" style="{{ $inputStyle }}">SVG</a>
                </div>
                <p class="text-[11px] mt-2" style="color:var(--sa-muted-fg);">PNG ≥ 2400 px avec marge blanche · SVG vectoriel pour l'imprimeur.</p>
            </div>

            <form method="POST" action="{{ route('super-admin.qr-campaigns.update', $campaign) }}" class="{{ $card }} space-y-3" style="{{ $cardStyle }}" x-data="{ target: '{{ old('target_type', $campaign->target_type) }}' }">
                @csrf @method('PUT')
                <p class="text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Modifier</p>
                <input name="name" value="{{ old('name', $campaign->name) }}" required maxlength="120" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                <select name="target_type" x-model="target" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    @foreach(QrCampaign::TARGET_LABELS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input name="target_path" x-show="target === '{{ QrCampaign::TARGET_INTERNAL_URL }}'" value="{{ old('target_path', $campaign->target_path) }}" placeholder="/tarifs" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                <input name="city" value="{{ old('city', $campaign->city) }}" placeholder="Ville" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                <input name="location" value="{{ old('location', $campaign->location) }}" placeholder="Emplacement" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                <button class="w-full h-10 rounded-xl text-sm font-medium text-white" style="background:#4f46e5;">Enregistrer</button>
            </form>

            <form method="POST" action="{{ route('super-admin.qr-campaigns.update', $campaign) }}">
                @csrf @method('PUT')
                <input type="hidden" name="toggle" value="1">
                <button class="w-full h-10 rounded-xl border text-sm font-medium" style="{{ $inputStyle }}">{{ $campaign->is_active ? 'Désactiver le lien' : 'Réactiver le lien' }}</button>
            </form>
        </div>
    </div>
</x-layouts.admin-super>
