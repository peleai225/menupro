{{-- Reusable lead card partial --}}
@php $compact = $compact ?? false; @endphp

<div class="group relative bg-gray-900 rounded-xl border border-gray-800/80 hover:border-gray-700 transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-black/20
            {{ $compact ? 'p-3' : 'p-3.5' }}
            {{ $compact ? '' : 'cursor-grab active:cursor-grabbing' }}"
     @if(!$compact)
         draggable="true"
         x-on:dragstart="onDragStart($event, {{ $lead->id }})"
     @endif
     data-lead-id="{{ $lead->id }}"
     wire:key="lead-{{ $lead->id }}"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100">

    {{-- Lead card content --}}
    <div class="flex items-start justify-between gap-2 mb-2">
        <h4 class="text-sm font-medium text-gray-100 {{ $compact ? 'truncate' : 'line-clamp-1' }}">
            {{ $lead->restaurant_name }}
        </h4>
        <span class="shrink-0 w-5 h-5 rounded-full bg-{{ $lead->source === \App\Enums\Crm\LeadSource::REFERRAL ? 'purple' : 'gray' }}-500/20 flex items-center justify-center">
            <span class="text-[10px] text-gray-400">{{ substr($lead->source->label(), 0, 1) }}</span>
        </span>
    </div>

    @if($lead->manager_name)
    <p class="text-xs text-gray-500 mb-1.5 {{ $compact ? 'truncate' : '' }}">{{ $lead->manager_name }}</p>
    @endif

    {{-- Plan badge --}}
    @if($lead->subscription_plan)
    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold mb-1.5 {{ $lead->subscription_plan->badgeClass() }}">
        {{ $lead->subscription_plan->shortLabel() }}
    </span>
    @endif

    <div class="flex items-center justify-between {{ $compact ? 'mt-2' : 'mt-3' }}">
        <div class="flex items-center gap-1.5">
            @if($lead->phone)
            <a href="tel:{{ $lead->phone }}"
               class="text-gray-500 hover:text-emerald-400 transition"
               onclick="event.stopPropagation()">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>
            @endif
            @if($lead->city)
            <span class="text-[10px] text-gray-600 bg-gray-800 px-1.5 py-0.5 rounded">{{ $lead->city }}</span>
            @endif
        </div>

        <div class="flex items-center gap-1.5">
            @if($lead->days_in_pipeline > 7)
            <span class="text-[10px] text-amber-500 font-medium">{{ $lead->days_in_pipeline }}j</span>
            @endif
            @if($lead->assignedUser)
            <img src="{{ $lead->assignedUser->avatar_url }}"
                 class="w-5 h-5 rounded-md object-cover border border-gray-700"
                 alt="{{ $lead->assignedUser->name }}"
                 title="{{ $lead->assignedUser->name }}">
            @endif
        </div>
    </div>

    {{-- Score indicator --}}
    @if($lead->score > 0)
    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $lead->score >= 70 ? 'text-emerald-400 bg-emerald-400/10' : ($lead->score >= 40 ? 'text-amber-400 bg-amber-400/10' : 'text-gray-500 bg-gray-500/10') }}">
            {{ $lead->score }}%
        </span>
    </div>
    @endif
</div>
