@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'hint' => null,
    'error' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'prefix' => null,
    'suffix' => null,
    'variant' => 'default',
])

@php
    $inputId = $name ?: Str::random(8);
    
    $baseClasses = 'w-full px-4 py-3 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:border-transparent';
    
    $variantClasses = match($variant) {
        'default' => 'bg-white border border-neutral-200 text-neutral-900 placeholder-neutral-400 focus:ring-primary-500',
        'dark' => 'bg-neutral-800 border border-neutral-700 text-white placeholder-neutral-500 focus:ring-primary-500',
        'filled' => 'bg-neutral-100 border border-transparent text-neutral-900 placeholder-neutral-500 focus:ring-primary-500 focus:bg-white focus:border-neutral-200',
        default => 'bg-white border border-neutral-200 text-neutral-900 placeholder-neutral-400 focus:ring-primary-500',
    };
    
    $errorClasses = $error ? 'border-red-500 focus:ring-red-500' : '';
    $iconClasses = $icon && $iconPosition === 'left' ? 'pl-12' : ($icon && $iconPosition === 'right' ? 'pr-12' : '');
    
    $classes = "$baseClasses $variantClasses $errorClasses $iconClasses";
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium {{ $variant === 'dark' ? 'text-neutral-300' : 'text-neutral-700' }} mb-2">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($icon && $iconPosition === 'left')
            <div class="absolute left-4 top-1/2 -translate-y-1/2 {{ $variant === 'dark' ? 'text-neutral-500' : 'text-neutral-400' }}">
                {!! $icon !!}
            </div>
        @endif

        @if($prefix)
            <div class="absolute left-4 top-1/2 -translate-y-1/2 {{ $variant === 'dark' ? 'text-neutral-500' : 'text-neutral-400' }} font-medium">
                {{ $prefix }}
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            {{ $attributes->except('class')->merge(['class' => $classes . ($prefix ? ' pl-12' : '') . ($suffix ? ' pr-12' : '')]) }}
        >

        @if($icon && $iconPosition === 'right')
            <div class="absolute right-4 top-1/2 -translate-y-1/2 {{ $variant === 'dark' ? 'text-neutral-500' : 'text-neutral-400' }}">
                {!! $icon !!}
            </div>
        @endif

        @if($suffix)
            <div class="absolute right-4 top-1/2 -translate-y-1/2 {{ $variant === 'dark' ? 'text-neutral-500' : 'text-neutral-400' }} font-medium">
                {{ $suffix }}
            </div>
        @endif
    </div>

    @if($hint && !$error)
        <p class="mt-2 text-sm {{ $variant === 'dark' ? 'text-neutral-500' : 'text-neutral-500' }}">
            {{ $hint }}
        </p>
    @endif

    @if($error)
        <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>

