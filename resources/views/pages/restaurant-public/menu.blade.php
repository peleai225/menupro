@php
    // Demo data - would come from database
    $restaurant = (object) [
        'name' => 'Le Délice',
        'slug' => $slug ?? 'le-delice',
        'description' => 'Restaurant africain et cuisine du monde',
        'logo_path' => null,
        'banner_path' => null,
        'address' => 'Cocody, Abidjan',
        'phone' => '+225 07 00 00 00 00',
        'isOpen' => true,
    ];
    
    $categories = collect([
        (object) ['id' => 1, 'name' => 'Entrées'],
        (object) ['id' => 2, 'name' => 'Plats principaux'],
        (object) ['id' => 3, 'name' => 'Grillades'],
        (object) ['id' => 4, 'name' => 'Accompagnements'],
        (object) ['id' => 5, 'name' => 'Boissons'],
        (object) ['id' => 6, 'name' => 'Desserts'],
    ]);
    
    $dishes = collect([
        (object) ['id' => 1, 'category_id' => 1, 'name' => 'Salade César', 'description' => 'Laitue fraîche, croûtons maison, parmesan, sauce césar', 'price' => 3500, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 2, 'category_id' => 1, 'name' => 'Nems au poulet', 'description' => 'Nems croustillants accompagnés de sauce aigre-douce', 'price' => 2500, 'image_path' => null, 'available' => true, 'badge' => 'Populaire'],
        (object) ['id' => 3, 'category_id' => 2, 'name' => 'Poulet braisé', 'description' => 'Poulet mariné et grillé au feu de bois, accompagné d\'alloco', 'price' => 5000, 'image_path' => null, 'available' => true, 'badge' => '🔥 Best-seller'],
        (object) ['id' => 4, 'category_id' => 2, 'name' => 'Attiéké poisson', 'description' => 'Attiéké frais avec poisson braisé et piment', 'price' => 4500, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 5, 'category_id' => 2, 'name' => 'Riz sauce graine', 'description' => 'Riz parfumé avec sauce graine et viande de bœuf', 'price' => 4000, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 6, 'category_id' => 3, 'name' => 'Brochettes de bœuf', 'description' => '4 brochettes de bœuf marinées, grillées à point', 'price' => 4500, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 7, 'category_id' => 3, 'name' => 'Côtes de porc', 'description' => 'Côtes de porc caramélisées au miel et épices', 'price' => 6000, 'image_path' => null, 'available' => false, 'badge' => null],
        (object) ['id' => 8, 'category_id' => 4, 'name' => 'Alloco', 'description' => 'Bananes plantains frites dorées', 'price' => 1500, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 9, 'category_id' => 4, 'name' => 'Frites maison', 'description' => 'Frites croustillantes assaisonnées', 'price' => 1500, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 10, 'category_id' => 5, 'name' => 'Bissap frais', 'description' => 'Jus d\'hibiscus rafraîchissant (50cl)', 'price' => 1000, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 11, 'category_id' => 5, 'name' => 'Gingembre', 'description' => 'Jus de gingembre fait maison (50cl)', 'price' => 1000, 'image_path' => null, 'available' => true, 'badge' => null],
        (object) ['id' => 12, 'category_id' => 6, 'name' => 'Dêguê', 'description' => 'Couscous de mil au lait caillé sucré', 'price' => 1500, 'image_path' => null, 'available' => true, 'badge' => 'Nouveau'],
    ]);
@endphp

<x-layouts.restaurant-public :restaurant="$restaurant" :categories="$categories">
    <!-- Menu Content -->
    <div class="space-y-12">
        @foreach($categories as $category)
            @php
                $categoryDishes = $dishes->where('category_id', $category->id);
            @endphp
            
            @if($categoryDishes->count() > 0)
                <section id="category-{{ $category->id }}">
                    <h2 class="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                        <span class="w-1 h-8 bg-primary-500 rounded-full"></span>
                        {{ $category->name }}
                    </h2>
                    
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($categoryDishes as $dish)
                            <x-dish-card :dish="$dish" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>

    <!-- Empty State (if no dishes) -->
    @if($dishes->count() === 0)
        <div class="text-center py-20">
            <div class="w-24 h-24 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-neutral-900 mb-2">Menu en préparation</h3>
            <p class="text-neutral-500">Le menu de ce restaurant sera bientôt disponible.</p>
        </div>
    @endif
</x-layouts.restaurant-public>

