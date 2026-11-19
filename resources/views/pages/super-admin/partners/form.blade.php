<x-layouts.admin-super title="Nouveau partenaire">
    <div class="mb-6">
        <a href="{{ route('super-admin.partners.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">← Partenaires</a>
        <h1 class="mt-1 text-2xl font-bold" style="color:var(--sa-fg);">Nouveau partenaire</h1>
        <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">La clé d'API et le secret de webhook seront affichés une seule fois après l'enregistrement.</p>
    </div>

    @include('pages.super-admin.parcels._flash')

    <form method="POST" action="{{ route('super-admin.partners.store') }}" class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        @csrf
        @include('pages.super-admin.partners._fields')
        <button class="mt-5 rounded-lg px-5 py-2 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Créer le partenaire</button>
    </form>
</x-layouts.admin-super>
