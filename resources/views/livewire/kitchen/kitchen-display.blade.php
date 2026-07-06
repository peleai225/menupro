<div id="kds-root" wire:poll.5s="loadOrders">

    {{-- COLONNES --}}
    @php
        $newOrders   = collect($orders)->whereIn('status', ['paid','confirmed'])->values();
        $prepOrders  = collect($orders)->where('status','preparing')->values();
        $readyOrders = collect($orders)->where('status','ready')->values();
    @endphp

    <div class="kds-main">

        {{-- NOUVELLE --}}
        <div class="kds-col kds-col-new">
            <div class="kds-col-header">
                <span class="kds-dot kds-dot-new"></span>
                Nouvelles
                <span class="kds-col-count">{{ $newOrders->count() }}</span>
            </div>
            <div class="kds-col-body">
                @forelse($newOrders as $order)
                    <div class="kds-card" wire:key="order-{{ $order['id'] }}">
                        <div class="kds-card-top kds-top-{{ $order['status'] }}">
                            <span class="kds-ref">#{{ $order['reference'] }}</span>
                            <span class="kds-badge kds-badge-{{ $order['status'] }}">
                                {{ $order['status'] === 'paid' ? 'NOUVELLE' : 'CONFIRMÉE' }}
                            </span>
                            @if($order['table_number'])
                                <span class="kds-table">Table {{ $order['table_number'] }}</span>
                            @endif
                            <span class="kds-timer {{ $order['minutes_ago'] > 20 ? 'kds-late' : ($order['minutes_ago'] > 10 ? 'kds-warn' : 'kds-ok') }}">
                                {{ $order['minutes_ago'] }}min
                            </span>
                        </div>
                        <div class="kds-card-body">
                            <div class="kds-customer">
                                <span>{{ $order['customer_name'] }}</span>
                                <span class="kds-type">{{ $order['type'] }}</span>
                            </div>
                            @foreach($order['items'] as $item)
                                <div class="kds-item">
                                    <span class="kds-qty">{{ $item['quantity'] }}x</span>
                                    <div>
                                        <span class="kds-name">{{ $item['name'] }}</span>
                                        @foreach($item['options'] as $opt)
                                            <span class="kds-opt">{{ is_string($opt) ? $opt : ($opt['name'] ?? '') }}</span>
                                        @endforeach
                                        @if($item['instructions'])
                                            <div class="kds-note">⚠ {{ $item['instructions'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if($order['status'] === 'paid')
                                <button class="kds-btn kds-btn-confirm"
                                    wire:click="confirm({{ $order['id'] }})"
                                    wire:loading.attr="disabled">
                                    ✓ Confirmer
                                </button>
                            @else
                                <button class="kds-btn kds-btn-prepare"
                                    wire:click="prepare({{ $order['id'] }})"
                                    wire:loading.attr="disabled">
                                    🍳 Commencer
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="kds-empty">Aucune commande</div>
                @endforelse
            </div>
        </div>

        {{-- EN PRÉPARATION --}}
        <div class="kds-col kds-col-prep">
            <div class="kds-col-header">
                <span class="kds-dot kds-dot-prep"></span>
                En préparation
                <span class="kds-col-count">{{ $prepOrders->count() }}</span>
            </div>
            <div class="kds-col-body">
                @forelse($prepOrders as $order)
                    <div class="kds-card" wire:key="order-{{ $order['id'] }}">
                        <div class="kds-card-top kds-top-preparing">
                            <span class="kds-ref">#{{ $order['reference'] }}</span>
                            <span class="kds-badge kds-badge-preparing">EN COURS</span>
                            @if($order['table_number'])
                                <span class="kds-table">Table {{ $order['table_number'] }}</span>
                            @endif
                            <span class="kds-timer {{ $order['minutes_ago'] > 20 ? 'kds-late' : ($order['minutes_ago'] > 10 ? 'kds-warn' : 'kds-ok') }}">
                                {{ $order['minutes_ago'] }}min
                            </span>
                        </div>
                        <div class="kds-card-body">
                            <div class="kds-customer">
                                <span>{{ $order['customer_name'] }}</span>
                                <span class="kds-type">{{ $order['type'] }}</span>
                            </div>
                            @foreach($order['items'] as $item)
                                <div class="kds-item">
                                    <span class="kds-qty">{{ $item['quantity'] }}x</span>
                                    <div>
                                        <span class="kds-name">{{ $item['name'] }}</span>
                                        @foreach($item['options'] as $opt)
                                            <span class="kds-opt">{{ is_string($opt) ? $opt : ($opt['name'] ?? '') }}</span>
                                        @endforeach
                                        @if($item['instructions'])
                                            <div class="kds-note">⚠ {{ $item['instructions'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <button class="kds-btn kds-btn-ready"
                                wire:click="ready({{ $order['id'] }})"
                                wire:loading.attr="disabled">
                                ✅ Prêt — Servir !
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="kds-empty">Aucune commande</div>
                @endforelse
            </div>
        </div>

        {{-- PRÊTES --}}
        <div class="kds-col kds-col-ready">
            <div class="kds-col-header">
                <span class="kds-dot kds-dot-ready"></span>
                Prêtes à servir
                <span class="kds-col-count">{{ $readyOrders->count() }}</span>
            </div>
            <div class="kds-col-body">
                @forelse($readyOrders as $order)
                    <div class="kds-card" wire:key="order-{{ $order['id'] }}">
                        <div class="kds-card-top kds-top-ready">
                            <span class="kds-ref">#{{ $order['reference'] }}</span>
                            <span class="kds-badge kds-badge-ready">PRÊT</span>
                            @if($order['table_number'])
                                <span class="kds-table">Table {{ $order['table_number'] }}</span>
                            @endif
                            <span style="font-size:11px;color:#4ade80;font-family:monospace;flex-shrink:0;">
                                {{ $order['ready_at'] ?? $order['created_at'] }}
                            </span>
                        </div>
                        <div class="kds-card-body">
                            <div class="kds-customer">
                                <span>{{ $order['customer_name'] }}</span>
                                <span class="kds-type">{{ $order['type'] }}</span>
                            </div>
                            @foreach($order['items'] as $item)
                                <div class="kds-item">
                                    <span class="kds-qty">{{ $item['quantity'] }}x</span>
                                    <div>
                                        <span class="kds-name">{{ $item['name'] }}</span>
                                        @foreach($item['options'] as $opt)
                                            <span class="kds-opt">{{ is_string($opt) ? $opt : ($opt['name'] ?? '') }}</span>
                                        @endforeach
                                        @if($item['instructions'])
                                            <div class="kds-note">⚠ {{ $item['instructions'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="kds-ready-label">En attente d'un serveur</div>
                        </div>
                    </div>
                @empty
                    <div class="kds-empty">Aucune commande</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
