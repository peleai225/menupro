{{--
    Pack de supports imprimés d'un plan (config('plans.packs'), source unique).
    Paramètres : $slug (plan), $compact (bool, version courte pour les cartes d'offres).
--}}
@php $pack = config('plans.packs.' . ($slug ?? '')); @endphp
@if($pack)
    @if($compact ?? false)
        <div class="mt-3 rounded-xl border border-dashed border-amber-300 bg-amber-50/70 px-3 py-2 text-left" data-plan-pack="{{ $slug }}">
            <p class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                {{ $pack['name'] }} <span class="font-semibold text-amber-700">— {{ $pack['note'] }}</span>
            </p>
            <p class="text-[11px] text-amber-900 mt-0.5 leading-snug">{{ implode(' + ', $pack['items']) }}</p>
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-4 text-left" data-plan-pack="{{ $slug }}">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-amber-900">{{ $pack['name'] }} <span class="text-sm font-semibold text-amber-700">— {{ $pack['note'] }}</span></p>
                    <ul class="mt-1 space-y-0.5 text-sm text-amber-900">
                        @foreach($pack['items'] as $packItem)
                            <li class="flex items-start gap-1.5"><span class="text-amber-500" aria-hidden="true">•</span>{{ $packItem }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endif
