@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'loading' => false,
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500 active:bg-primary-700',
        'secondary' => 'bg-neutral-800 text-white hover:bg-neutral-700 focus:ring-neutral-500 active:bg-neutral-900',
        'outline' => 'border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white focus:ring-primary-500',
        'outline-dark' => 'border-2 border-neutral-700 text-neutral-300 hover:bg-neutral-700 hover:text-white focus:ring-neutral-500',
        'ghost' => 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 focus:ring-neutral-500',
        'ghost-dark' => 'text-neutral-400 hover:bg-neutral-800 hover:text-white focus:ring-neutral-500',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600 focus:ring-accent-500 active:bg-accent-700',
        'success' => 'bg-secondary-500 text-white hover:bg-secondary-600 focus:ring-secondary-500',
        'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500',
        'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
        'link' => 'text-primary-500 hover:text-primary-600 hover:underline p-0',
        default => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500',
    };
    
    $sizeClasses = match($size) {
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
        'xl' => 'px-10 py-5 text-xl',
        'icon-sm' => 'p-2',
        'icon' => 'p-3',
        'icon-lg' => 'p-4',
        default => 'px-6 py-3 text-base',
    };
    
    $classes = "$baseClasses $variantClasses $sizeClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @else
            @if($icon && $iconPosition === 'left')
                {!! $icon !!}
            @endif
            {{ $slot }}
            @if($icon && $iconPosition === 'right')
                {!! $icon !!}
            @endif
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled || $loading]) }}>
        @if($loading)
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @else
            @if($icon && $iconPosition === 'left')
                {!! $icon !!}
            @endif
            {{ $slot }}
            @if($icon && $iconPosition === 'right')
                {!! $icon !!}
            @endif
        @endif
    </button>
@endif

