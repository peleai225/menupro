@props([
    'variant' => 'default',
    'hover' => false,
    'padding' => true,
])

@php
    $baseClasses = 'rounded-2xl overflow-hidden';
    
    $variantClasses = match($variant) {
        'default' => 'bg-white shadow-card border border-neutral-100',
        'dark' => 'bg-neutral-900 border border-neutral-800',
        'elevated' => 'bg-white shadow-elevated',
        'outline' => 'bg-white border-2 border-neutral-200',
        'glass' => 'glass',
        'glass-dark' => 'glass-dark',
        'gradient' => 'bg-gradient-primary text-white',
        default => 'bg-white shadow-card border border-neutral-100',
    };
    
    $hoverClasses = $hover ? 'transition-all duration-300 hover:shadow-elevated hover:-translate-y-1 cursor-pointer' : '';
    $paddingClasses = $padding ? 'p-6' : '';
    
    $classes = "$baseClasses $variantClasses $hoverClasses $paddingClasses";
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="border-b {{ $variant === 'dark' ? 'border-neutral-800' : 'border-neutral-100' }} {{ $padding ? '-mx-6 -mt-6 px-6 py-4 mb-6' : 'px-6 py-4' }}">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if(isset($footer))
        <div class="border-t {{ $variant === 'dark' ? 'border-neutral-800' : 'border-neutral-100' }} {{ $padding ? '-mx-6 -mb-6 px-6 py-4 mt-6' : 'px-6 py-4' }} bg-neutral-50">
            {{ $footer }}
        </div>
    @endif
</div>

