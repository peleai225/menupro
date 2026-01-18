@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'removable' => false,
])

@php
    $baseClasses = 'inline-flex items-center font-semibold rounded-full';
    
    $variantClasses = match($variant) {
        'default' => 'bg-neutral-100 text-neutral-700',
        'primary' => 'bg-primary-100 text-primary-700',
        'secondary' => 'bg-neutral-800 text-neutral-100',
        'success' => 'bg-secondary-100 text-secondary-700',
        'warning' => 'bg-yellow-100 text-yellow-700',
        'error' => 'bg-red-100 text-red-700',
        'danger' => 'bg-red-100 text-red-700',
        'info' => 'bg-blue-100 text-blue-700',
        'accent' => 'bg-accent-100 text-accent-700',
        // Solid variants
        'primary-solid' => 'bg-primary-500 text-white',
        'success-solid' => 'bg-secondary-500 text-white',
        'warning-solid' => 'bg-yellow-500 text-white',
        'error-solid' => 'bg-red-500 text-white',
        'info-solid' => 'bg-blue-500 text-white',
        // Outline variants
        'primary-outline' => 'border border-primary-500 text-primary-500 bg-transparent',
        'success-outline' => 'border border-secondary-500 text-secondary-500 bg-transparent',
        default => 'bg-neutral-100 text-neutral-700',
    };
    
    $sizeClasses = match($size) {
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base',
        default => 'px-3 py-1 text-sm',
    };
    
    $dotClasses = match($variant) {
        'success', 'success-solid' => 'bg-secondary-500',
        'warning', 'warning-solid' => 'bg-yellow-500',
        'error', 'danger', 'error-solid' => 'bg-red-500',
        'info', 'info-solid' => 'bg-blue-500',
        'primary', 'primary-solid' => 'bg-primary-500',
        default => 'bg-neutral-500',
    };
    
    $classes = "$baseClasses $variantClasses $sizeClasses";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }} mr-1.5 {{ str_contains($variant, 'solid') ? 'bg-white/70' : '' }}"></span>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="ml-1.5 -mr-1 hover:opacity-70 transition-opacity">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</span>

