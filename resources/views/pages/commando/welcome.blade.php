<x-layouts.app title="Bienvenue - Définir votre mot de passe">
    <div class="min-h-screen flex items-center justify-center p-4 bg-[#0f172a]">
        <div class="w-full max-w-md bg-slate-800/50 border border-slate-700 rounded-2xl p-8">
            <h1 class="text-xl font-bold text-white mb-2">Bienvenue sur MenuPro Commando</h1>
            <p class="text-slate-400 text-sm mb-6">Définissez votre mot de passe pour accéder à votre espace agent.</p>

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-red-400 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('commando.welcome.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-600 bg-slate-800/80 text-white px-4 py-3 focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-600 bg-slate-800/80 text-white px-4 py-3 focus:ring-2 focus:ring-orange-500">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white">
                    Définir mon mot de passe et continuer
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
