<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
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
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_dishes' => ['required', 'integer', 'min:1'],
            'max_categories' => ['required', 'integer', 'min:1'],
            'max_employees' => ['required', 'integer', 'min:1'],
            'max_orders_per_month' => ['nullable', 'integer', 'min:1'],
            'has_delivery' => ['boolean'],
            'has_stock_management' => ['boolean'],
            'has_analytics' => ['boolean'],
            'has_custom_domain' => ['boolean'],
            'has_priority_support' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $data = $request->all();
        $data['sort_order'] = Plan::max('sort_order') + 1;

        Plan::create($data);

        return back()->with('success', 'Plan créé avec succès.');
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_dishes' => ['required', 'integer', 'min:1'],
            'max_categories' => ['required', 'integer', 'min:1'],
            'max_employees' => ['required', 'integer', 'min:1'],
            'max_orders_per_month' => ['nullable', 'integer', 'min:1'],
            'has_delivery' => ['boolean'],
            'has_stock_management' => ['boolean'],
            'has_analytics' => ['boolean'],
            'has_custom_domain' => ['boolean'],
            'has_priority_support' => ['boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $plan->update($request->all());

        return back()->with('success', 'Plan mis à jour.');
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

