<div wire:poll.8s="checkNew"
     x-data="{
         audioCtx: null,
         audioUnlocked: false,
         unlockAudio() {
             const unlock = () => {
                 if (this.audioUnlocked) return;
                 this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                 if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                 this.audioUnlocked = true;
                 document.removeEventListener('click', unlock);
                 document.removeEventListener('touchstart', unlock);
             };
             document.addEventListener('click', unlock, { once: true });
             document.addEventListener('touchstart', unlock, { once: true });
         },
         playAlert() {
             if (!this.audioUnlocked || !this.audioCtx) return;
             try {
                 if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                 const ctx = this.audioCtx;
                 [660, 880, 660, 880].forEach((freq, i) => {
                     setTimeout(() => {
                         const o = ctx.createOscillator(), g = ctx.createGain();
                         o.connect(g); g.connect(ctx.destination);
                         o.frequency.value = freq;
                         o.type = 'sine';
                         g.gain.setValueAtTime(0.4, ctx.currentTime);
                         g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                         o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.25);
                     }, i * 150);
                 });
             } catch(e) {}
         }
     }"
     x-init="unlockAudio()"
     @new-service-request.window="playAlert()">

    @if($requests->isEmpty())
        {{-- Rien à afficher si aucun appel --}}
    @else
        <div class="card overflow-hidden border-l-4 border-violet-500">
            <div class="p-4 bg-violet-50 border-b border-violet-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-violet-500 rounded-xl flex items-center justify-center flex-shrink-0 animate-pulse">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-violet-900">Appels du personnel</h3>
                        <p class="text-xs text-violet-600">{{ $requests->count() }} demande{{ $requests->count() > 1 ? 's' : '' }} en attente</p>
                    </div>
                </div>
                <span class="w-7 h-7 bg-violet-500 text-white text-sm font-bold rounded-full flex items-center justify-center">
                    {{ $requests->count() }}
                </span>
            </div>

            <div class="divide-y divide-neutral-100">
                @foreach($requests as $req)
                    <div class="flex items-center gap-4 p-4 hover:bg-neutral-50 transition-colors" wire:key="svc-{{ $req->id }}">
                        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0 text-lg">
                            {{ $req->typeIcon() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-neutral-900">{{ $req->table_number }}</span>
                                <span class="text-sm text-violet-700 font-medium">{{ $req->typeLabel() }}</span>
                            </div>
                            @if($req->notes)
                                <p class="text-sm text-neutral-500 truncate mt-0.5">"{{ $req->notes }}"</p>
                            @endif
                            <p class="text-xs text-neutral-400 mt-0.5">
                                il y a {{ $req->created_at->diffForHumans(['locale' => 'fr']) }}
                            </p>
                        </div>
                        <button wire:click="markDone({{ $req->id }})"
                                wire:loading.attr="disabled"
                                wire:target="markDone({{ $req->id }})"
                                class="flex-shrink-0 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-1.5">
                            <span wire:loading.remove wire:target="markDone({{ $req->id }})">✓ Traité</span>
                            <span wire:loading wire:target="markDone({{ $req->id }})">...</span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
