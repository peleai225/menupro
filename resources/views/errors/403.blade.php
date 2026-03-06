@extends('errors.layout')

@section('title', 'Accès refusé')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">403</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Accès refusé</h1>
    <p class="text-neutral-500 mb-6">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
</div>
@endsection
