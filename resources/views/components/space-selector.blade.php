{{-- resources/views/components/space-selector.blade.php --}}
@if($restaurant->hasMultiSpaces() && $restaurant->spaces()->active()->count() > 0)
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="flex items-center gap-2 px-3 py-2 bg-white border border-neutral-200 rounded-xl text-sm font-medium text-neutral-700 hover:border-neutral-300 transition shadow-sm">
        @if($currentSpaceId)
            @php $activeSpace = $restaurant->spaces->find($currentSpaceId) @endphp
            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $activeSpace?->color ?? '#6366f1' }}"></span>
            <span>{{ $activeSpace?->name ?? 'Espace' }}</span>
        @else
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Tous les espaces</span>
        @endif
        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute top-full left-0 mt-1 bg-white border border-neutral-200 rounded-xl shadow-lg z-50 min-w-[180px] py-1">
        {{-- Tous les espaces --}}
        <form method="POST" action="{{ route('restaurant.spaces.select') }}">
            @csrf
            <input type="hidden" name="space_id" value="">
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm hover:bg-neutral-50 flex items-center gap-2 {{ !$currentSpaceId ? 'font-semibold text-primary-600' : 'text-neutral-700' }}">
                <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Tous les espaces
            </button>
        </form>
        @foreach($restaurant->spaces()->active()->get() as $space)
        <form method="POST" action="{{ route('restaurant.spaces.select') }}">
            @csrf
            <input type="hidden" name="space_id" value="{{ $space->id }}">
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm hover:bg-neutral-50 flex items-center gap-2 {{ $currentSpaceId == $space->id ? 'font-semibold text-primary-600' : 'text-neutral-700' }}">
                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $space->color }}"></span>
                {{ $space->name }}
            </button>
        </form>
        @endforeach
    </div>
</div>
@endif
