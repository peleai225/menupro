@extends('errors.layout')

@section('title', 'Session expirée')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">419</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Session expirée</h1>
    <p class="text-neutral-500 mb-6">Votre session a expiré. Veuillez actualiser la page et réessayer.</p>
</div>
@endsection
