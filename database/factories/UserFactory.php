<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Explicite (plutôt que de compter sur le défaut DB) : sans ça,
            // l'instance en mémoire renvoyée par create() a is_active=null
            // jusqu'au prochain refresh(), ce que lit désormais EnsureSuperAdmin.
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Super admin back-office (role gardé par $guarded → assigné après make).
     */
    public function superAdmin(): static
    {
        return $this->afterMaking(fn (User $user) => $user->role = UserRole::SUPER_ADMIN);
    }

    /**
     * Administrateur d'un restaurant donné.
     */
    public function restaurantAdmin(Restaurant $restaurant): static
    {
        return $this->afterMaking(function (User $user) use ($restaurant) {
            $user->role = UserRole::RESTAURANT_ADMIN;
            $user->restaurant_id = $restaurant->id;
        });
    }
}
