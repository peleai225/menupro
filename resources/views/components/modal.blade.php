@props([
    'name' => 'modal',
    'title' => null,
    'size' => 'md',
    'closeable' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-[calc(100vw-2rem)] sm:max-w-sm',
        'md' => 'max-w-[calc(100vw-2rem)] sm:max-w-md',
        'lg' => 'max-w-[calc(100vw-2rem)] sm:max-w-lg',
        'xl' => 'max-w-[calc(100vw-2rem)] sm:max-w-xl',
        '2xl' => 'max-w-[calc(100vw-2rem)] sm:max-w-2xl',
        '3xl' => 'max-w-[calc(100vw-2rem)] sm:max-w-3xl',
        '4xl' => 'max-w-[calc(100vw-2rem)] sm:max-w-4xl',
        'full' => 'max-w-[calc(100vw-2rem)] sm:max-w-full sm:mx-4',
        default => 'max-w-[calc(100vw-2rem)] sm:max-w-md',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="{{ $closeable ? 'open = false' : '' }}"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm"
    ></div>

    <!-- Modal Container: bottom-sheet sur mobile, centré sur desktop -->
    <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            @click.stop
            class="w-full {{ $sizeClasses }} mx-auto bg-white rounded-t-2xl sm:rounded-2xl shadow-elevated overflow-hidden max-h-[90vh] overflow-y-auto"
        >
            <!-- Header -->
            @if($title || $closeable)
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
                    @if($title)
                        <h3 class="text-lg font-semibold text-neutral-900" id="modal-title">
                            {{ $title }}
                        </h3>
                    @else
                        <div></div>
                    @endif

                    @if($closeable)
                        <button
                            @click="open = false"
                            class="p-2 rounded-lg text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <!-- Content -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

