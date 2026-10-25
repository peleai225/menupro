{{-- Formulaire de plan (création / modification). $plan = null pour une création. --}}
@php
    $unlimited = fn ($v) => $v === null || $v >= 9999;
    // Champ vide = illimité (stocké 9999) ; on n'affiche donc jamais 9999 dans le champ
    $limitValue = fn ($field) => $plan && !$unlimited($plan->{$field}) ? $plan->{$field} : '';
    $inputClass = 'w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500';
    $inputStyle = 'background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);';
    $checked = fn ($field, $default = false) => ($plan ? $plan->{$field} : $default) ? 'checked' : '';
@endphp

<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Nom du plan *</label>
        <input type="text" name="name" required value="{{ $plan?->name }}" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Prix mensuel (FCFA) *</label>
        <input type="number" name="price" required min="0" value="{{ $plan?->price }}" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Prix hebdo (FCFA)</label>
        <input type="number" name="weekly_price" min="1" value="{{ $plan?->weekly_price }}" placeholder="Stand uniquement" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Durée (jours) *</label>
        <input type="number" name="duration_days" required min="1" value="{{ $plan?->duration_days ?? 30 }}" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max plats</label>
        <input type="number" name="max_dishes" min="1" value="{{ $limitValue('max_dishes') }}" placeholder="Vide = illimité" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max catégories</label>
        <input type="number" name="max_categories" min="1" value="{{ $limitValue('max_categories') }}" placeholder="Vide = illimité" class="{{ $inputClass }}" style="{{ $inputStyle }}">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max employés</label>
        <input type="number" name="max_employees" min="0" value="{{ $limitValue('max_employees') }}" placeholder="Vide = illimité" class="{{ $inputClass }}" style="{{ $inputStyle }}">
        <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">0 = le gérant seul</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Quota commandes/mois</label>
        <input type="number" name="max_orders_per_month" min="1" value="{{ $limitValue('max_orders_per_month') }}" placeholder="Vide = illimité" class="{{ $inputClass }}" style="{{ $inputStyle }}">
        <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">Souple : alerte à 80 %/100 %, jamais bloquant</p>
    </div>
    <div class="col-span-2">
        <p class="text-xs p-2 rounded-lg" style="background:var(--sa-muted);color:var(--sa-muted-fg);">
            Laissez une limite <strong>vide</strong> pour « illimité ». Ne saisissez jamais 0 pour illimité : 0 signifie zéro.
        </p>
    </div>
    <div class="col-span-2">
        <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Description</label>
        <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="{{ $inputStyle }}">{{ $plan?->description }}</textarea>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 pt-4 border-t" style="border-color:var(--sa-border);">
    @foreach([
        'has_delivery' => ['Livraison MenuPro', true],
        'has_stock_management' => ['Stock simple', false],
        'has_ingredient_stock' => ['Stock par ingrédient', false],
        'has_analytics' => ['Statistiques', false],
        'has_hotel_rooms' => ['Chambres d\'hôtel', false],
        'has_multi_spaces' => ['Multi-espaces', false],
        'has_priority_support' => ['Support prioritaire', false],
        'has_custom_domain' => ['Domaine personnalisé', false],
    ] as $field => [$label, $default])
        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
            <input type="checkbox" name="{{ $field }}" value="1" {{ $checked($field, $default) }} class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
            <span class="text-sm" style="color:var(--sa-fg);">{{ $label }}</span>
        </label>
    @endforeach
    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
        <input type="checkbox" name="is_featured" value="1" {{ $checked('is_featured') }} class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
        <span class="text-sm" style="color:var(--sa-fg);">Mis en avant</span>
    </label>
    @if($plan)
        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
            <input type="checkbox" name="is_active" value="1" {{ $checked('is_active') }} class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
            <span class="text-sm" style="color:var(--sa-fg);">En vente (actif)</span>
        </label>
    @endif
</div>
