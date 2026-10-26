@props(['item', 'collapsible' => false, 'withId' => true])
{{-- Onglet du tableau de bord (App\Support\RestaurantNavigation) : normal, ou verrouillé (cadenas + « Passer au plan X ») --}}
@php $locked = $item['state'] === \App\Support\RestaurantNavigation::STATE_LOCKED; @endphp
<a href="{{ $item['url'] }}"
   @if($withId && $item['id'] && !$locked) id="{{ $item['id'] }}" @endif
   data-nav="{{ $item['key'] }}" data-nav-state="{{ $item['state'] }}"
   @if($locked) title="Inclus à partir du plan {{ $item['upgrade_plan']?->name }} — passer au plan {{ $item['upgrade_plan']?->name }}" @endif
   @if($collapsible && !$locked) :title="!expanded ? @js($item['label']) : ''" @endif
   class="sidebar-item min-h-[44px] {{ $item['is_active'] ? 'sidebar-item-active' : '' }} {{ $locked ? 'opacity-70 hover:opacity-100' : '' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon_path'] }}"/>
    </svg>
    <span @if($collapsible) x-show="expanded" x-transition @endif class="whitespace-nowrap flex-1 min-w-0 truncate">{{ $item['label'] }}</span>
    @if($locked)
        <span @if($collapsible) x-show="expanded" @endif class="ml-auto flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 whitespace-nowrap" data-lock>
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            <span class="sr-only">Verrouillé — passer au plan</span>{{ $item['upgrade_plan']?->name ?? 'Plan supérieur' }}
        </span>
    @elseif($item['count'] > 0)
        <span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $item['count'] }}</span>
    @endif
</a>
