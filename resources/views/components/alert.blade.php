@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
    'icon' => true,
])

@php
    $baseClasses = 'rounded-xl p-4';
    
    $variantClasses = match($variant) {
        'success' => 'bg-secondary-50 border border-secondary-200 text-secondary-800',
        'warning' => 'bg-yellow-50 border border-yellow-200 text-yellow-800',
        'error' => 'bg-red-50 border border-red-200 text-red-800',
        'danger' => 'bg-red-50 border border-red-200 text-red-800',
        'info' => 'bg-blue-50 border border-blue-200 text-blue-800',
        'neutral' => 'bg-neutral-100 border border-neutral-200 text-neutral-800',
        // Dark variants
        'success-dark' => 'bg-secondary-500/10 border border-secondary-500/20 text-secondary-400',
        'warning-dark' => 'bg-yellow-500/10 border border-yellow-500/20 text-yellow-400',
        'error-dark' => 'bg-red-500/10 border border-red-500/20 text-red-400',
        'info-dark' => 'bg-blue-500/10 border border-blue-500/20 text-blue-400',
        default => 'bg-blue-50 border border-blue-200 text-blue-800',
    };
    
    $iconColor = match($variant) {
        'success', 'success-dark' => 'text-secondary-500',
        'warning', 'warning-dark' => 'text-yellow-500',
        'error', 'danger', 'error-dark' => 'text-red-500',
        'info', 'info-dark' => 'text-blue-500',
        default => 'text-blue-500',
    };
    
    $classes = "$baseClasses $variantClasses";
@endphp

<div x-data="{ show: true }" x-show="show" x-transition {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex gap-3">
        @if($icon)
            <div class="flex-shrink-0 {{ $iconColor }}">
                @switch($variant)
                    @case('success')
                    @case('success-dark')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @break
                    @case('warning')
                    @case('warning-dark')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        @break
                    @case('error')
                    @case('danger')
                    @case('error-dark')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @break
                    @default
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                @endswitch
            </div>
        @endif

        <div class="flex-1">
            @if($title)
                <h4 class="font-semibold mb-1">{{ $title }}</h4>
            @endif
            <div class="text-sm {{ $title ? 'opacity-90' : '' }}">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button @click="show = false" class="flex-shrink-0 hover:opacity-70 transition-opacity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>

