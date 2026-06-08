<?php

namespace App\Livewire\Restaurant;

use App\Enums\ExpenseCategory;
use App\Models\Expense;
use App\Services\RevenueCalculator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Expenses extends Component
{
    use WithPagination;

    #[Url]
    public string $period = 'month';

    #[Url]
    public string $categoryFilter = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $category = '';
    public string $description = '';
    public int $amount = 0;
    public string $expense_date = '';
    public string $supplier = '';
    public string $reference = '';
    public bool $is_recurring = false;
    public string $recurrence_period = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->expense_date = today()->format('Y-m-d');
    }

    #[Computed]
    public function expenses()
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) return collect();

        [$from, $to] = $this->getPeriodDates();

        return Expense::where('restaurant_id', $restaurant->id)
            ->forPeriod($from, $to)
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->latest('expense_date')
            ->paginate(20);
    }

    #[Computed]
    public function summary(): array
    {
        $restaurant = auth()->user()->restaurant;
        if (!$restaurant) {
            return ['total_expenses' => 0, 'gross_revenue' => 0, 'net_revenue' => 0, 'profit' => 0, 'margin' => 0, 'by_category' => []];
        }

        [$from, $to] = $this->getPeriodDates();

        $totalExpenses = Expense::where('restaurant_id', $restaurant->id)
            ->forPeriod($from, $to)
            ->sum('amount');

        $revenue = RevenueCalculator::for($restaurant->id, $from->startOfDay(), $to->endOfDay());
        $grossRevenue = $revenue->grossRevenue();
        $netRevenue = $revenue->netRevenue();
        $profit = $netRevenue - $totalExpenses;
        $margin = $grossRevenue > 0 ? round(($profit / $grossRevenue) * 100, 1) : 0;

        $byCategory = Expense::where('restaurant_id', $restaurant->id)
            ->forPeriod($from, $to)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'category' => ExpenseCategory::from($row->category),
                'total' => (int) $row->total,
                'count' => $row->count,
                'percent' => $totalExpenses > 0 ? round(($row->total / $totalExpenses) * 100, 1) : 0,
            ]);

        return [
            'total_expenses' => (int) $totalExpenses,
            'gross_revenue' => $grossRevenue,
            'net_revenue' => $netRevenue,
            'profit' => $profit,
            'margin' => $margin,
            'by_category' => $byCategory,
            'orders_count' => $revenue->validOrdersCount(),
        ];
    }

    public function openModal(?int $id = null): void
    {
        if ($id) {
            $expense = Expense::where('restaurant_id', auth()->user()->restaurant_id)->findOrFail($id);
            $this->editingId = $id;
            $this->category = $expense->category->value;
            $this->description = $expense->description;
            $this->amount = $expense->amount;
            $this->expense_date = $expense->expense_date->format('Y-m-d');
            $this->supplier = $expense->supplier ?? '';
            $this->reference = $expense->reference ?? '';
            $this->is_recurring = $expense->is_recurring;
            $this->recurrence_period = $expense->recurrence_period ?? '';
            $this->notes = $expense->notes ?? '';
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'category' => 'required|in:' . implode(',', array_column(ExpenseCategory::cases(), 'value')),
            'description' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'expense_date' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'is_recurring' => 'boolean',
            'recurrence_period' => 'nullable|in:daily,weekly,monthly,yearly',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            ...$validated,
            'restaurant_id' => auth()->user()->restaurant_id,
            'user_id' => auth()->id(),
        ];

        if ($this->editingId) {
            Expense::where('restaurant_id', auth()->user()->restaurant_id)
                ->where('id', $this->editingId)
                ->update($data);
        } else {
            Expense::create($data);
        }

        $this->closeModal();
        unset($this->expenses, $this->summary);
    }

    public function delete(int $id): void
    {
        Expense::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('id', $id)
            ->delete();

        unset($this->expenses, $this->summary);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->category = '';
        $this->description = '';
        $this->amount = 0;
        $this->expense_date = today()->format('Y-m-d');
        $this->supplier = '';
        $this->reference = '';
        $this->is_recurring = false;
        $this->recurrence_period = '';
        $this->notes = '';
    }

    private function getPeriodDates(): array
    {
        return match ($this->period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function render()
    {
        return view('livewire.restaurant.expenses')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Dépenses & Rentabilité',
            ]);
    }
}
