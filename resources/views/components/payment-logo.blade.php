@props(['method' => ''])

@php
    $method = strtolower($method);
@endphp

@if($method === 'wave')
    {{-- Wave - logo local (public/images/payments/wave.svg) ou fallback badge texte --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-[#0052FF] flex-shrink-0" title="Wave">
        @if(file_exists(public_path('images/payments/wave.svg')))
            <img src="{{ asset('images/payments/wave.svg') }}" alt="Wave" class="h-7 w-7 object-contain">
        @else
            <span class="text-white font-bold text-xs">Wave</span>
        @endif
    </span>
@elseif($method === 'orange')
    {{-- Orange Money - logo local (public/images/payments/orange-money.svg) ou fallback badge texte --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-black flex-shrink-0" title="Orange Money">
        @if(file_exists(public_path('images/payments/orange-money.svg')))
            <img src="{{ asset('images/payments/orange-money.svg') }}" alt="Orange Money" class="h-7 w-7 object-contain">
        @else
            <span class="text-orange-400 font-bold text-[10px] leading-none">OM</span>
        @endif
    </span>
@elseif($method === 'mtn')
    {{-- MTN MoMo - badge jaune comme sur la home --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-yellow-500 flex-shrink-0" title="MTN MoMo">
        <span class="text-black font-bold text-xs">MTN</span>
    </span>
@elseif($method === 'moov')
    {{-- Moov Money - bleu Moov --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-[#0066CC] flex-shrink-0" title="Moov Money">
        <span class="text-white font-bold text-xs">Moov</span>
    </span>
@elseif($method === 'fusionpay')
    {{-- FusionPay / MoneyFusion - Mobile Money --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-100 flex-shrink-0" title="FusionPay (Wave, Orange, MTN)">
        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
    </span>
@elseif(in_array($method, ['lygos', 'geniuspay', 'online']))
    {{-- Paiement en ligne --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 flex-shrink-0" title="Paiement en ligne">
        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
    </span>
@elseif($method === 'cash_on_delivery' || $method === 'cash')
    {{-- Paiement à la caisse --}}
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 flex-shrink-0" title="Paiement à la caisse">
        <svg class="w-6 h-6 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </span>
@else
    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-neutral-100 flex-shrink-0">
        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </span>
@endif
