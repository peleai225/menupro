@extends('errors.layout')

@section('title', 'Accès refusé')

@section('content')
<div class="animate-slide-up">
    <div class="text-8xl font-display font-bold text-primary-500/20 mb-4">403</div>
    <h1 class="font-display text-2xl md:text-3xl font-bold text-neutral-900 mb-2">Accès refusé</h1>
    <p class="text-neutral-500 mb-6">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <p class="text-sm text-neutral-400 mb-6">
        Si vous avez cliqué sur un lien de notification (ex. « Voir la commande »), assurez-vous d'être connecté avec le compte du restaurant concerné.
    </p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        @auth
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 border border-neutral-300 text-neutral-700 hover:bg-neutral-50 font-medium rounded-xl transition-colors">
                    Se déconnecter
                </button>
            </form>
        @endauth
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors">
            Se connecter
        </a>
    </div>
</div>
@endsection
