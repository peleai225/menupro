@extends('errors.layout')

@section('title', 'Trop de requêtes')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">429</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Trop de requêtes</h1>
    <p class="text-neutral-500 mb-6">Vous avez effectué trop de requêtes. Veuillez patienter avant de réessayer.</p>
</div>
@endsection
