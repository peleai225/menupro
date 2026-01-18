<?php

namespace App\Livewire\Restaurant;

use App\Models\Review;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Reviews extends Component
{
    use WithPagination;

    public ?Review $editingReview = null;
    public bool $showResponseModal = false;
    public bool $showStats = true;

    #[Rule('required|string|min:1|max:1000')]
    public string $response = '';

    public string $search = '';
    public string $filter = 'all'; // all, approved, pending, rejected
    public string $ratingFilter = 'all'; // all, 5, 4, 3, 2, 1

    public function mount(): void
    {
        //
    }

    public function openResponseModal(Review $review): void
    {
        $this->editingReview = $review;
        $this->response = $review->response ?? '';
        $this->showResponseModal = true;
    }

    public function closeResponseModal(): void
    {
        $this->showResponseModal = false;
        $this->editingReview = null;
        $this->response = '';
    }

    public function saveResponse(): void
    {
        $this->validate();

        if (!$this->editingReview) {
            return;
        }

        try {
            $this->editingReview->respond($this->response);
            session()->flash('success', 'Réponse enregistrée avec succès.');
            $this->closeResponseModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function approve(Review $review): void
    {
        try {
            $review->approve();
            session()->flash('success', 'Avis approuvé avec succès.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function reject(Review $review): void
    {
        try {
            $review->reject();
            session()->flash('success', 'Avis rejeté avec succès.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function delete(Review $review): void
    {
        try {
            $review->delete();
            session()->flash('success', 'Avis supprimé avec succès.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function getStatsProperty(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return [];
        }

        $reviews = Review::where('restaurant_id', $restaurant->id);

        $total = $reviews->count();
        $approved = $reviews->where('is_approved', true)->count();
        $pending = $reviews->where('is_approved', false)->count();
        $averageRating = $reviews->where('is_approved', true)->avg('rating') ?? 0;

        // Ratings distribution
        $ratingsDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingsDistribution[$i] = $reviews->where('is_approved', true)->where('rating', $i)->count();
        }

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'average_rating' => round($averageRating, 1),
            'ratings_distribution' => $ratingsDistribution,
        ];
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return view('livewire.restaurant.reviews')
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Avis Clients',
                    'restaurant' => null,
                    'subscription' => null,
                ]);
        }

        $query = Review::where('restaurant_id', $restaurant->id);

        // Search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                  ->orWhere('comment', 'like', '%' . $this->search . '%');
            });
        }

        // Filter
        switch ($this->filter) {
            case 'approved':
                $query->where('is_approved', true);
                break;
            case 'pending':
                $query->where('is_approved', false);
                break;
            case 'rejected':
                $query->where('is_approved', false)->where('is_visible', false);
                break;
        }

        // Rating filter
        if ($this->ratingFilter !== 'all') {
            $query->where('rating', (int) $this->ratingFilter);
        }

        $reviews = $query->latest()->paginate(15);

        $stats = $this->stats;
        $subscription = $restaurant->activeSubscription;

        return view('livewire.restaurant.reviews', [
            'reviews' => $reviews,
            'stats' => $stats,
        ])
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Avis Clients',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

