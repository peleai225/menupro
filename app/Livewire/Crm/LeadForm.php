<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\LeadSource;
use App\Enums\Crm\SubscriptionPlan;
use App\Models\Crm\Lead;
use App\Services\Crm\LeadPipelineService;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadForm extends Component
{
    public ?Lead $lead = null;

    public string $restaurant_name = '';
    public string $manager_name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $source = 'terrain';
    public string $subscription_plan = '';
    public ?float $latitude = null;
    public ?float $longitude = null;

    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'restaurant_name' => 'required|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'source' => 'required|in:' . implode(',', array_column(LeadSource::cases(), 'value')),
            'subscription_plan' => 'nullable|in:' . implode(',', array_column(SubscriptionPlan::cases(), 'value')),
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    #[On('open-lead-form')]
    public function open(?int $leadId = null): void
    {
        if ($leadId) {
            $this->lead = Lead::findOrFail($leadId);

            $user = auth()->user();
            $canEdit = in_array($user->role->value, ['super_admin', 'team_leader'])
                || $this->lead->assigned_to === $user->id;
            if (!$canEdit) {
                abort(403);
            }

            $this->fill($this->lead->only([
                'restaurant_name', 'manager_name', 'phone', 'email',
                'address', 'city', 'latitude', 'longitude',
            ]));
            $this->source = $this->lead->source->value;
            $this->subscription_plan = $this->lead->subscription_plan?->value ?? '';
        } else {
            $this->reset(['lead', 'restaurant_name', 'manager_name', 'phone', 'email', 'address', 'city', 'source', 'subscription_plan', 'latitude', 'longitude']);
            $this->source = 'terrain';
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // Normaliser subscription_plan vide en null
        if (empty($data['subscription_plan'])) {
            $data['subscription_plan'] = null;
        }

        if ($this->lead) {
            $this->lead->update($data);
            $this->dispatch('lead-updated');
            $this->dispatch('crm-notify', message: 'Lead mis à jour.', type: 'success');
        } else {
            $data['assigned_to'] = auth()->id();
            app(LeadPipelineService::class)->createLead($data, auth()->user());
            $this->dispatch('lead-created');
            $this->dispatch('crm-notify', message: 'Lead créé avec succès !', type: 'lead');
        }

        $this->showModal = false;
        $this->reset();
    }

    public function render()
    {
        return view('livewire.crm.lead-form');
    }
}
