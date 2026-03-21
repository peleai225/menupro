<x-layouts.restaurant-public :restaurant="$restaurant" :hide-header="false">
    <div class="min-h-screen bg-gradient-to-b from-neutral-100 to-neutral-50 flex items-center justify-center py-16 px-4">
        <div class="max-w-md w-full">
            <!-- Card principale -->
            <div class="bg-white rounded-3xl shadow-xl shadow-neutral-200/60 overflow-hidden border border-neutral-100">
                <!-- Header coloré -->
                <div class="relative bg-gradient-to-br from-neutral-800 to-neutral-900 px-8 pt-10 pb-16 text-center overflow-hidden">
                    <!-- Décoration -->
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/5 rounded-full"></div>

                    @if($restaurant->logo_url)
                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                             class="w-20 h-20 rounded-2xl object-cover mx-auto mb-4 border-2 border-white/20 shadow-lg relative z-10">
                    @else
                        <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 relative z-10">
                            <svg class="w-10 h-10 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    @endif
                    <h2 class="text-xl font-bold text-white relative z-10">{{ $restaurant->name }}</h2>
                </div>

                <!-- Icône status (overlap) -->
                <div class="flex justify-center -mt-8 relative z-10">
                    <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="px-8 pb-8 pt-6 text-center">
                    <!-- Message -->
                    <h1 class="text-2xl font-bold text-neutral-900 mb-2">Commandes indisponibles</h1>
                    <p class="text-neutral-500 text-sm leading-relaxed">
                        {{ $message ?? 'Ce restaurant n\'accepte pas de commandes pour le moment.' }}
                    </p>

                    <!-- Prochaine ouverture si connue -->
                    @if(!empty($nextOpening))
                        <div class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 bg-primary-50 border border-primary-200 rounded-xl text-sm text-primary-700 font-medium">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Prochaine ouverture : <strong>{{ $nextOpening }}</strong>
                        </div>
                    @endif

                    <!-- Horaires -->
                    @if($restaurant->opening_hours)
                        @php
                            $days = [
                                'monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi',
                                'thursday' => 'Jeudi', 'friday' => 'Vendredi',
                                'saturday' => 'Samedi', 'sunday' => 'Dimanche',
                            ];
                            $hours = is_string($restaurant->opening_hours)
                                ? json_decode($restaurant->opening_hours, true)
                                : $restaurant->opening_hours;
                            $today = strtolower(now()->locale('en')->dayName);
                        @endphp
                        <div class="mt-6 bg-neutral-50 rounded-2xl p-5 text-left border border-neutral-100">
                            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-4 text-center">Horaires d'ouverture</p>
                            <div class="space-y-1.5">
                                @foreach($days as $key => $label)
                                    @php $day = $hours[$key] ?? null; $isToday = $key === $today; @endphp
                                    <div class="flex justify-between items-center py-1.5 px-3 rounded-lg text-sm {{ $isToday ? 'bg-primary-50 border border-primary-100' : '' }}">
                                        <span class="font-medium {{ $isToday ? 'text-primary-700' : 'text-neutral-600' }}">
                                            {{ $label }}
                                            @if($isToday)
                                                <span class="ml-1 text-[10px] bg-primary-500 text-white px-1.5 py-0.5 rounded-full font-semibold">Aujourd'hui</span>
                                            @endif
                                        </span>
                                        <span class="{{ $isToday ? 'text-primary-700 font-semibold' : (empty($day['open']) ? 'text-neutral-400' : 'text-neutral-800 font-medium') }}">
                                            @if(empty($day['open']))
                                                Fermé
                                            @else
                                                {{ $day['open'] }} – {{ $day['close'] }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Retour au menu -->
                    <a href="{{ route('r.menu', $restaurant->slug) }}"
                       class="mt-6 w-full inline-flex items-center justify-center gap-2 btn btn-primary py-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Voir le menu
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.restaurant-public>
