@extends('errors.layout')

@section('title', 'Erreur serveur')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">500</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Erreur serveur</h1>
    <p class="text-neutral-500 mb-6">Une erreur interne s'est produite. Notre équipe a été notifiée.</p>
</div>
@endsection
