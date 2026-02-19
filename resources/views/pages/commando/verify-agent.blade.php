<x-layouts.app title="{{ $valid ? 'Agent vérifié' : 'Agent invalide' }}">
    <div class="min-h-screen flex items-center justify-center p-4 {{ $valid ? 'bg-[#0f172a]' : 'bg-neutral-900' }}">
        <div class="max-w-sm w-full bg-slate-800/50 border border-slate-700 rounded-2xl p-8 text-center">
            @if($valid)
                <div class="w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-white mb-1">Agent valide</h1>
                <p class="text-slate-400 text-sm mb-4">{{ $message }}</p>
                @if($agent)
                    <p class="text-orange-400 font-medium">{{ $agent->full_name }}</p>
                    <p class="text-slate-500 text-xs mt-1">MenuPro Commando</p>
                @endif
            @else
                <div class="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-white mb-1">Agent invalide</h1>
                <p class="text-slate-400 text-sm">{{ $message }}</p>
            @endif
        </div>
    </div>
</x-layouts.app>
