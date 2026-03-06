@extends('errors.layout')

@section('title', 'Non authentifié')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">401</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Non authentifié</h1>
    <p class="text-neutral-500 mb-6">Vous devez vous connecter pour accéder à cette page.</p>
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 text-primary-600 hover:text-primary-700 font-semibold rounded-xl transition-colors mb-4">
        Se connecter
    </a>
</div>
@endsection
