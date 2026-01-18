@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'icon' => null,
    'variant' => 'default',
])

@php
    $bgClasses = match($variant) {
        'default' => 'bg-white',
        'dark' => 'bg-neutral-800',
        'primary' => 'bg-gradient-primary text-white',
        'success' => 'bg-secondary-500 text-white',
        default => 'bg-white',
    };
    
    $textClasses = match($variant) {
        'primary', 'success' => 'text-white/70',
        'dark' => 'text-neutral-400',
        default => 'text-neutral-500',
    };
    
    $valueClasses = match($variant) {
        'primary', 'success' => 'text-white',
        'dark' => 'text-white',
        default => 'text-neutral-900',
    };
    
    $changeClasses = match($changeType) {
        'increase' => 'text-secondary-500 bg-secondary-50',
        'decrease' => 'text-red-500 bg-red-50',
        default => 'text-neutral-500 bg-neutral-100',
    };
@endphp

<div {{ $attributes->merge(['class' => "$bgClasses rounded-2xl p-6 shadow-card border border-neutral-100"]) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium {{ $textClasses }}">{{ $title }}</p>
            <p class="text-3xl font-bold {{ $valueClasses }} mt-2">{{ $value }}</p>
            
            @if($change !== null)
                <div class="flex items-center gap-1.5 mt-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $changeClasses }}">
                        @if($changeType === 'increase')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        @elseif($changeType === 'decrease')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        @endif
                        {{ $change }}
                    </span>
                    <span class="text-xs {{ $textClasses }}">vs mois dernier</span>
                </div>
            @endif
        </div>

        @if($icon)
            <div class="p-3 rounded-xl {{ $variant === 'default' ? 'bg-primary-50 text-primary-500' : 'bg-white/10 text-white' }}">
                {!! $icon !!}
            </div>
        @endif
    </div>

    @if(isset($footer))
        <div class="mt-4 pt-4 border-t {{ $variant === 'dark' ? 'border-neutral-700' : 'border-neutral-100' }}">
            {{ $footer }}
        </div>
    @endif
</div>

