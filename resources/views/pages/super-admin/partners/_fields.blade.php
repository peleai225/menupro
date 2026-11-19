@php
    $in = 'mt-1 w-full rounded-lg px-3 py-2 text-sm';
    $inStyle = 'border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);';
    $lbl = 'block text-xs font-medium';
    $selectedCities = old('cities', $partner->cities ?? []);
@endphp
<div class="grid gap-4 sm:grid-cols-2">
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Nom *
        <input name="name" required maxlength="120" value="{{ old('name', $partner->name) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }} flex items-end gap-2" style="color:var(--sa-fg);">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $partner->is_active))> Compte actif (clé utilisable)
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Contact
        <input name="contact_name" maxlength="120" value="{{ old('contact_name', $partner->contact_name) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Téléphone
        <input name="contact_phone" maxlength="30" value="{{ old('contact_phone', $partner->contact_phone) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">E-mail
        <input type="email" name="contact_email" maxlength="150" value="{{ old('contact_email', $partner->contact_email) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">URL du webhook (https)
        <input type="url" name="webhook_url" maxlength="500" value="{{ old('webhook_url', $partner->webhook_url) }}" placeholder="https://…" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <div class="sm:col-span-2">
        <p class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Villes autorisées (aucune cochée = toutes)</p>
        <div class="mt-1 flex flex-wrap gap-3">
            @foreach($cities as $city)
                <label class="inline-flex items-center gap-1.5 text-sm" style="color:var(--sa-fg);">
                    <input type="checkbox" name="cities[]" value="{{ $city }}" @checked(in_array($city, (array) $selectedCities, true))> {{ $city }}
                </label>
            @endforeach
        </div>
    </div>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Remise sur le tarif ville (%)
        <input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ old('discount_percent', $partner->discount_percent) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Limite de requêtes / minute
        <input type="number" min="1" max="1000" name="rate_limit_per_minute" value="{{ old('rate_limit_per_minute', $partner->rate_limit_per_minute) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Grille propre : base (F, vide = ville)
        <input type="number" min="0" name="pricing_base_fee" value="{{ old('pricing_base_fee', $partner->pricing['base_fee'] ?? null) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }}" style="color:var(--sa-muted-fg);">Grille propre : prix/km (F, vide = ville)
        <input type="number" min="0" name="pricing_per_km_fee" value="{{ old('pricing_per_km_fee', $partner->pricing['per_km_fee'] ?? null) }}" class="{{ $in }}" style="{{ $inStyle }}">
    </label>
    <label class="{{ $lbl }} sm:col-span-2" style="color:var(--sa-muted-fg);">Notes internes
        <textarea name="notes" rows="2" maxlength="2000" class="{{ $in }}" style="{{ $inStyle }}">{{ old('notes', $partner->notes) }}</textarea>
    </label>
</div>
