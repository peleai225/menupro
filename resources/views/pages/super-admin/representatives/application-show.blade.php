@php
    use App\Models\PartnerApplication;
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
    $card = 'rounded-xl p-5 border shadow-sm';
    $cardStyle = 'background:var(--sa-card);border-color:var(--sa-border);';
    $profile = $application->user?->representativeProfile;
@endphp
<x-layouts.admin-super title="Candidature — {{ $application->full_name }}">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('super-admin.partner-applications.index') }}" style="color:var(--sa-muted-fg);" aria-label="Retour aux candidatures">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $application->full_name }}</h1>
        <span class="text-sm" style="color:var(--sa-muted-fg);">{{ $application->city }} · reçue le {{ $application->created_at->format('d/m/Y à H:i') }}</span>
    </div>

    @include('pages.super-admin.representatives._nav')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @foreach([
                        'Téléphone' => $application->phone,
                        'E-mail' => $application->email ?: '—',
                        'Ville' => $application->city,
                        'Statut légal' => $application->legal_status_label,
                        'Disponibilité' => $application->availability_label,
                        'Statut' => $application->status_label,
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs font-medium" style="color:var(--sa-muted-fg);">{{ $label }}</dt>
                            <dd class="font-semibold" style="color:var(--sa-fg);">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if($application->attachment_path)
                    <a href="{{ route('super-admin.partner-applications.attachment', $application) }}" class="inline-flex items-center gap-2 mt-4 text-sm font-medium underline" style="color:#f97316;">
                        📎 Télécharger la pièce jointe ({{ $application->attachment_name }})
                    </a>
                @endif
            </div>

            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <p class="text-xs font-bold uppercase mb-2" style="color:var(--sa-muted-fg);">Expérience et réseau</p>
                <p class="text-sm whitespace-pre-line" style="color:var(--sa-fg);">{{ $application->experience }}</p>
                <p class="text-xs font-bold uppercase mt-5 mb-2" style="color:var(--sa-muted-fg);">Motivation</p>
                <p class="text-sm whitespace-pre-line" style="color:var(--sa-fg);">{{ $application->motivation }}</p>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Statut + notes internes --}}
            <form method="POST" action="{{ route('super-admin.partner-applications.update', $application) }}" class="{{ $card }} space-y-3" style="{{ $cardStyle }}">
                @csrf @method('PUT')
                <p class="text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Suivi</p>
                <select name="status" class="w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    @foreach(PartnerApplication::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <textarea name="internal_notes" rows="5" placeholder="Notes internes (entretien, références…)" class="w-full px-3 py-2 rounded-xl border text-sm" style="{{ $inputStyle }}">{{ old('internal_notes', $application->internal_notes) }}</textarea>
                @if($application->reviewer)
                    <p class="text-xs" style="color:var(--sa-muted-fg);">Dernière mise à jour par {{ $application->reviewer->name }} le {{ $application->reviewed_at?->format('d/m/Y H:i') }}</p>
                @endif
                <button class="w-full h-10 rounded-xl text-sm font-medium text-white" style="background:#f97316;">Enregistrer</button>
            </form>

            {{-- Création du représentant --}}
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <p class="text-xs font-bold uppercase mb-3" style="color:var(--sa-muted-fg);">Représentant</p>
                @if($profile)
                    <p class="text-sm mb-3" style="color:var(--sa-fg);">Compte créé : {{ $application->user->email }}</p>
                    <a href="{{ route('super-admin.representatives.show', $profile) }}" class="inline-flex h-10 px-4 items-center rounded-xl text-sm font-medium text-white" style="background:#4f46e5;">Voir la fiche représentant</a>
                @elseif($application->isAccepted())
                    <form method="POST" action="{{ route('super-admin.partner-applications.create-representative', $application) }}" class="space-y-3">
                        @csrf
                        <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Ville (territoire)
                            <input name="city" value="{{ old('city', $application->city) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">% abonnements
                                <input name="subscription_rate" type="number" step="0.01" min="0" max="100" value="{{ old('subscription_rate', $defaults['subscription_rate_percent']) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                            </label>
                            <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">% livraisons
                                <input name="delivery_rate" type="number" step="0.01" min="0" max="100" value="{{ old('delivery_rate', $defaults['delivery_rate_percent']) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Fin d'essai
                                <input name="trial_ends_at" type="date" value="{{ old('trial_ends_at', now()->addMonthsNoOverflow($defaults['trial_months'])->toDateString()) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                            </label>
                            <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Objectif / trimestre
                                <input name="quarterly_target" type="number" min="0" value="{{ old('quarterly_target', $defaults['default_quarterly_target']) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                            </label>
                        </div>
                        <label class="flex items-center gap-2 text-sm" style="color:var(--sa-fg);">
                            <input type="checkbox" name="exclusivity_active" value="1" @checked(old('exclusivity_active'))>
                            Exclusivité de la ville active
                        </label>
                        <button class="w-full h-10 rounded-xl text-sm font-medium text-white" style="background:#16a34a;">Créer le représentant</button>
                    </form>
                @else
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Passez la candidature au statut « Acceptée » pour pouvoir créer le représentant.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin-super>
