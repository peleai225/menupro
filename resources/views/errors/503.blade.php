@extends('errors.layout')

@section('title', 'Service indisponible')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">503</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Service indisponible</h1>
    <p class="text-neutral-500 mb-6">Nous effectuons une maintenance. Merci de réessayer dans quelques instants.</p>
</div>
@endsection
