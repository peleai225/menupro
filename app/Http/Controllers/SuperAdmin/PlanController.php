<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    /** Valeur stockée pour « illimité » (convention : >= 9999 = illimité, 0 = zéro) */
    private const UNLIMITED = 9999;

    /** Limites où un champ vide signifie « illimité » */
    private const LIMIT_FIELDS = ['max_dishes', 'max_categories', 'max_employees', 'max_orders_per_month'];

    private const BOOLEAN_FIELDS = [
        'has_delivery', 'has_stock_management', 'has_ingredient_stock', 'has_analytics',
        'has_custom_domain', 'has_priority_support', 'has_hotel_rooms', 'has_multi_spaces',
        'is_featured',
    ];

    /**
     * Display a listing of plans.
     */
    public function index(): View
    {
        $plans = Plan::withCount(['restaurants', 'subscriptions'])
            ->ordered()
            ->get();

        return view('pages.super-admin.plans', compact('plans'));
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPlanData($request);
        $data['sort_order'] = Plan::max('sort_order') + 1;

        Plan::create($data);

        return back()->with('success', 'Plan créé avec succès.');
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $this->validatedPlanData($request, withActive: true);

        $plan->update($data);

        return back()->with('success', 'Plan mis à jour.');
    }

    /**
     * Validation commune création / modification.
     *
     * Limites : champ VIDE = illimité (stocké 9999). 0 n'est jamais « illimité » : il est refusé
     * pour les plats, catégories et commandes ; pour les employés, 0 = le gérant seul.
     * Cases à cocher : une case décochée vaut false (avant, elle était ignorée à la mise à jour).
     */
    private function validatedPlanData(Request $request, bool $withActive = false): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'weekly_price' => ['nullable', 'integer', 'min:1'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_dishes' => ['nullable', 'integer', 'min:1'],
            'max_categories' => ['nullable', 'integer', 'min:1'],
            'max_employees' => ['nullable', 'integer', 'min:0'],
            'max_orders_per_month' => ['nullable', 'integer', 'min:1'],
        ], [
            'max_dishes.min' => 'Laissez le champ vide pour « illimité » (0 signifierait zéro plat).',
            'max_categories.min' => 'Laissez le champ vide pour « illimité » (0 signifierait zéro catégorie).',
            'max_orders_per_month.min' => 'Laissez le champ vide pour « illimité ».',
        ]);

        foreach (self::LIMIT_FIELDS as $field) {
            $validated[$field] = $validated[$field] ?? self::UNLIMITED;
        }

        foreach (self::BOOLEAN_FIELDS as $field) {
            $validated[$field] = $request->boolean($field);
        }

        if ($withActive) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        return $validated;
    }

    /**
     * Toggle plan active status.
     */
    public function toggle(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Plan {$status}.");
    }

    /**
     * Reorder plans.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:plans,id'],
        ]);

        foreach ($request->order as $index => $planId) {
            Plan::where('id', $planId)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Ordre des plans mis à jour.');
    }

    /**
     * Delete a plan.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Ce plan a des abonnements actifs et ne peut pas être supprimé.');
        }

        $plan->delete();

        return back()->with('success', 'Plan supprimé.');
    }
}
