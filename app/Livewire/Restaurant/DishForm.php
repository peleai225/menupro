<?php

namespace App\Livewire\Restaurant;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Services\MediaUploader;
use App\Services\PlanLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class DishForm extends Component
{
    use WithFileUploads;

    public ?Dish $dish = null;

    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:1000')]
    public ?string $description = null;

    #[Rule('required|numeric|min:0')]
    public float $price = 0;

    #[Rule('required|exists:categories,id')]
    public ?int $category_id = null;

    #[Rule('nullable|image|max:5120')]
    public $image = null;

    public ?string $existingImage = null;

    public bool $is_available = true;
    public bool $is_featured = false;

    #[Rule('nullable|integer|min:1')]
    public ?int $prep_time = null;

    #[Rule('nullable|string|max:500')]
    public ?string $allergens = null;

    // Nutritional info
    public ?int $calories = null;
    public ?float $proteins = null;
    public ?float $carbs = null;
    public ?float $fats = null;

    // Ingredients for stock management
    public array $selectedIngredients = [];

    // Option groups
    public array $optionGroups = [];

    public function mount(?Dish $dish = null): void
    {
        if ($dish && $dish->exists) {
            $this->dish = $dish;
            $this->name = $dish->name;
            $this->description = $dish->description;
            $this->price = $dish->price;
            $this->category_id = $dish->category_id;
            $this->existingImage = $dish->image_path;
            $this->is_available = $dish->is_available;
            $this->is_featured = $dish->is_featured;
            $this->prep_time = $dish->prep_time;
            $this->allergens = $dish->allergens;
            $this->calories = $dish->calories;
            $this->proteins = $dish->proteins;
            $this->carbs = $dish->carbs;
            $this->fats = $dish->fats;

            // Load ingredients
            $this->selectedIngredients = $dish->ingredients->map(fn($i) => [
                'id' => $i->id,
                'quantity' => $i->pivot->quantity,
            ])->toArray();

            // Load option groups
            $this->optionGroups = $dish->optionGroups->map(fn($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'is_required' => $g->is_required,
                'max_selections' => $g->max_selections,
                'options' => $g->options->map(fn($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'price_adjustment' => $o->price_adjustment,
                    'is_available' => $o->is_available,
                ])->toArray(),
            ])->toArray();
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::where('restaurant_id', auth()->user()->restaurant_id)
            ->active()
            ->ordered()
            ->get();
    }

    #[Computed]
    public function ingredients()
    {
        $restaurant = auth()->user()->restaurant;
        
        if (!$restaurant->currentPlan?->has_stock_management) {
            return collect();
        }

        return Ingredient::where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function canAddDish(): bool
    {
        if ($this->dish) {
            return true; // Editing existing dish
        }

        return app(PlanLimiter::class)
            ->forRestaurant(auth()->user()->restaurant)
            ->canAddDish();
    }

    public function addOptionGroup(): void
    {
        $this->optionGroups[] = [
            'id' => null,
            'name' => '',
            'is_required' => false,
            'max_selections' => 1,
            'options' => [],
        ];
    }

    public function removeOptionGroup(int $index): void
    {
        unset($this->optionGroups[$index]);
        $this->optionGroups = array_values($this->optionGroups);
    }

    public function addOption(int $groupIndex): void
    {
        $this->optionGroups[$groupIndex]['options'][] = [
            'id' => null,
            'name' => '',
            'price_adjustment' => 0,
            'is_available' => true,
        ];
    }

    public function removeOption(int $groupIndex, int $optionIndex): void
    {
        unset($this->optionGroups[$groupIndex]['options'][$optionIndex]);
        $this->optionGroups[$groupIndex]['options'] = array_values($this->optionGroups[$groupIndex]['options']);
    }

    public function addIngredient(): void
    {
        $this->selectedIngredients[] = [
            'id' => null,
            'quantity' => 1,
        ];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->selectedIngredients[$index]);
        $this->selectedIngredients = array_values($this->selectedIngredients);
    }

    public function save(): void
    {
        $this->validate();

        if (!$this->canAddDish && !$this->dish) {
            session()->flash('error', 'Vous avez atteint la limite de plats pour votre forfait.');
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'is_available' => $this->is_available,
            'is_featured' => $this->is_featured,
            'prep_time' => $this->prep_time,
            'allergens' => $this->allergens,
            'calories' => $this->calories,
            'proteins' => $this->proteins,
            'carbs' => $this->carbs,
            'fats' => $this->fats,
        ];

        // Handle image upload
        if ($this->image) {
            try {
                $uploader = app(MediaUploader::class);
                $restaurantId = auth()->user()->restaurant_id;
                $data['image_path'] = $uploader->upload($this->image, "restaurants/{$restaurantId}/dishes");

                // Delete old image if editing
                if ($this->dish && $this->existingImage) {
                    Storage::disk('public')->delete($this->existingImage);
                }
                $this->existingImage = $data['image_path'];
                $this->image = null;
            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                return;
            }
        }

        if ($this->dish) {
            $this->dish->update($data);
            $dish = $this->dish;
        } else {
            // Check quota before creating
            $restaurant = auth()->user()->restaurant;
            $planLimiter = app(PlanLimiter::class)->forRestaurant($restaurant);
            
            try {
                $planLimiter->validateOrFail('dishes');
            } catch (\App\Exceptions\QuotaExceededException $e) {
                session()->flash('error', $e->getMessage());
                return;
            }
            
            $data['restaurant_id'] = auth()->user()->restaurant_id;
            $data['sort_order'] = Dish::where('restaurant_id', auth()->user()->restaurant_id)->max('sort_order') + 1;
            $dish = Dish::create($data);
        }

        // Sync ingredients
        $ingredientSync = [];
        foreach ($this->selectedIngredients as $item) {
            if (!empty($item['id'])) {
                $ingredientSync[$item['id']] = ['quantity' => $item['quantity'] ?? 1];
            }
        }
        $dish->ingredients()->sync($ingredientSync);

        // Sync option groups
        $existingGroupIds = [];
        foreach ($this->optionGroups as $groupData) {
            if (!empty($groupData['id'])) {
                // Update existing group
                $group = \App\Models\DishOptionGroup::where('id', $groupData['id'])
                    ->where('restaurant_id', auth()->user()->restaurant_id)
                    ->first();
                
                if ($group) {
                    // Attach if not already attached
                    if (!$dish->optionGroups()->where('dish_option_groups.id', $group->id)->exists()) {
                        $dish->optionGroups()->attach($group->id);
                    }
                    
                    $group->update([
                        'name' => $groupData['name'],
                        'is_required' => $groupData['is_required'],
                        'max_selections' => $groupData['max_selections'],
                    ]);
                    $existingGroupIds[] = $group->id;
                }
            } else {
                // Create new group
                $group = \App\Models\DishOptionGroup::create([
                    'restaurant_id' => auth()->user()->restaurant_id,
                    'name' => $groupData['name'],
                    'is_required' => $groupData['is_required'],
                    'max_selections' => $groupData['max_selections'],
                ]);
                $dish->optionGroups()->attach($group->id);
                $existingGroupIds[] = $group->id;
            }

            // Sync options
            $existingOptionIds = [];
            foreach ($groupData['options'] ?? [] as $optionData) {
                if (!empty($optionData['id'])) {
                    $option = $group->options()->find($optionData['id']);
                    if ($option) {
                        $option->update([
                            'name' => $optionData['name'],
                            'price_adjustment' => $optionData['price_adjustment'] ?? 0,
                            'is_active' => $optionData['is_available'] ?? true,
                        ]);
                        $existingOptionIds[] = $option->id;
                    }
                } else {
                    $option = $group->options()->create([
                        'name' => $optionData['name'],
                        'price_adjustment' => $optionData['price_adjustment'] ?? 0,
                        'is_active' => $optionData['is_available'] ?? true,
                    ]);
                    $existingOptionIds[] = $option->id;
                }
            }

            // Delete removed options
            $group->options()->whereNotIn('id', $existingOptionIds)->delete();
        }

        // Detach removed option groups from this dish
        $currentGroupIds = $dish->optionGroups()->pluck('id')->toArray();
        $groupsToDetach = array_diff($currentGroupIds, $existingGroupIds);
        
        if (!empty($groupsToDetach)) {
            $dish->optionGroups()->detach($groupsToDetach);
            
            // Delete groups that are no longer used by any dish
            foreach ($groupsToDetach as $groupId) {
                $group = \App\Models\DishOptionGroup::find($groupId);
                if ($group && $group->dishes()->count() === 0) {
                    $group->delete();
                }
            }
        }

        session()->flash('success', $this->dish ? 'Plat mis à jour avec succès.' : 'Plat créé avec succès.');
        
        $this->redirect(route('restaurant.plats.index'));
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        
        return view('livewire.restaurant.dish-form')
            ->layout('components.layouts.admin-restaurant', [
                'title' => $this->dish ? 'Modifier le plat' : 'Nouveau plat',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

