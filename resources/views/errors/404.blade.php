@extends('errors.layout')

@section('title', 'Page introuvable')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">404</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Page introuvable</h1>
    <p class="text-neutral-500 mb-6">Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>
</div>
@endsection
