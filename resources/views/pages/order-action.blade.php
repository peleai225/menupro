@php
    $awaiting = $order->isAwaitingAcceptance();
    $since = $order->acceptanceRequestedAt();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Commande #{{ $order->reference }} — {{ $order->restaurant?->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #f5f5f4; color: #1c1917; min-height: 100vh; -webkit-tap-highlight-color: transparent; }
        .wrap { max-width: 480px; margin: 0 auto; padding: 16px; }
        .card { background: #fff; border-radius: 16px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 12px; }
        .resto { font-size: 13px; color: #78716c; font-weight: 600; }
        h1 { font-size: 22px; font-weight: 900; margin: 2px 0 6px; }
        .meta { font-size: 14px; color: #57534e; line-height: 1.5; }
        .timer { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 999px; background: #fef3c7; color: #92400e; font-weight: 800; font-size: 14px; }
        .timer.late { background: #fee2e2; color: #b91c1c; }
        .items { list-style: none; }
        .items li { display: flex; justify-content: space-between; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f5f5f4; font-size: 15px; }
        .items li:last-child { border-bottom: none; }
        .qty { font-weight: 900; color: #ea580c; margin-right: 6px; }
        .note { font-size: 13px; color: #b45309; margin-top: 2px; }
        .total { display: flex; justify-content: space-between; font-weight: 900; font-size: 18px; padding-top: 10px; }
        .label { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #78716c; margin-bottom: 8px; }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chips input { position: absolute; opacity: 0; pointer-events: none; }
        .chips label { padding: 12px 16px; min-width: 64px; text-align: center; border: 2px solid #e7e5e4; border-radius: 12px; font-weight: 800; font-size: 16px; cursor: pointer; background: #fff; }
        .chips input:checked + label { border-color: #16a34a; background: #dcfce7; color: #166534; }
        .reasons label { display: block; padding: 12px 14px; border: 2px solid #e7e5e4; border-radius: 12px; margin-bottom: 8px; font-weight: 700; cursor: pointer; }
        .reasons input { margin-right: 8px; }
        textarea { width: 100%; border: 2px solid #e7e5e4; border-radius: 12px; padding: 10px; font: inherit; margin-bottom: 12px; }
        .btn { display: block; width: 100%; padding: 16px; border: none; border-radius: 14px; font-size: 18px; font-weight: 900; color: #fff; cursor: pointer; }
        .btn:active { transform: scale(.98); }
        .btn-accept { background: #16a34a; }
        .btn-reject { background: #dc2626; }
        .btn-ghost { background: transparent; color: #b91c1c; border: 2px solid #fecaca; font-size: 15px; padding: 12px; margin-top: 10px; }
        .flash { padding: 14px; border-radius: 12px; font-weight: 700; margin-bottom: 12px; }
        .flash.success { background: #dcfce7; color: #166534; }
        .flash.info { background: #e0f2fe; color: #075985; }
        .flash.error { background: #fee2e2; color: #991b1b; }
        .state { text-align: center; padding: 24px 16px; }
        .state .big { font-size: 44px; }
        .state p { margin-top: 6px; color: #57534e; }
        .link { display: block; text-align: center; margin-top: 14px; color: #ea580c; font-weight: 700; text-decoration: none; }
        [hidden] { display: none !important; }
    </style>
</head>
<body>
<div class="wrap">
    @if(session('success'))
        <div class="flash success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="flash info">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="flash error">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="resto">{{ $order->restaurant?->name }}</div>
        <h1>Commande #{{ $order->reference }}</h1>
        <div class="meta">
            👤 {{ $order->customer_name }}
            @if($order->customer_phone) · 📞 {{ $order->customer_phone }} @endif
            <br>📦 {{ $order->type?->label() ?? '' }}
            @if($order->delivery_address) → {{ $order->delivery_address }} @endif
            @if($order->table_number) · Table {{ $order->table_number }} @endif
            <br>💳 {{ $order->isPaidOnline() ? 'Payée en ligne' : 'Paiement à la livraison / sur place' }}
        </div>
        @if($awaiting)
            <span class="timer" id="timer" data-since="{{ $since->timestamp }}">⏱ Reçue il y a …</span>
        @endif
    </div>

    <div class="card">
        <ul class="items">
            @foreach($order->items as $item)
                <li>
                    <div>
                        <span class="qty">{{ $item->quantity }}×</span>{{ $item->dish_name }}
                        @if($item->special_instructions)
                            <div class="note">📝 {{ $item->special_instructions }}</div>
                        @endif
                    </div>
                    <div>{{ number_format($item->total_price, 0, ',', ' ') }}</div>
                </li>
            @endforeach
        </ul>
        @if($order->customer_notes)
            <div class="note" style="margin-top:8px">💬 {{ $order->customer_notes }}</div>
        @endif
        <div class="total"><span>Total</span><span>{{ number_format($order->total, 0, ',', ' ') }} F CFA</span></div>
    </div>

    @if($awaiting)
        {{-- Accepter --}}
        <form method="POST" action="{{ $actionUrl }}" class="card" id="accept-form">
            @csrf
            <input type="hidden" name="decision" value="accept">
            <div class="label">Temps de préparation</div>
            <div class="chips">
                @foreach($prepChoices as $minutes)
                    <input type="radio" name="prep_minutes" id="prep-{{ $minutes }}" value="{{ $minutes }}" @checked($minutes === $defaultPrep)>
                    <label for="prep-{{ $minutes }}">{{ $minutes }} min</label>
                @endforeach
            </div>
            <button type="submit" class="btn btn-accept">✅ Accepter la commande</button>
            <button type="button" class="btn btn-ghost" id="show-reject">Refuser la commande…</button>
        </form>

        {{-- Refuser (motif obligatoire) --}}
        <form method="POST" action="{{ $actionUrl }}" class="card" id="reject-form" @if(!$errors->has('reason') && !$errors->has('note')) hidden @endif>
            @csrf
            <input type="hidden" name="decision" value="reject">
            <div class="label">Motif du refus</div>
            <div class="reasons">
                @foreach($reasons as $reason)
                    <label><input type="radio" name="reason" value="{{ $reason->value }}" required @checked(old('reason') === $reason->value)> {{ $reason->label() }}</label>
                @endforeach
            </div>
            <textarea name="note" rows="2" maxlength="255" placeholder="Précision (obligatoire pour « Autre motif »)">{{ old('note') }}</textarea>
            <button type="submit" class="btn btn-reject" data-confirm="Refuser cette commande ? Le client sera prévenu.">❌ Refuser la commande</button>
        </form>
    @else
        <div class="card state">
            @if($order->status === \App\Enums\OrderStatus::CANCELLED)
                <div class="big">❌</div>
                <strong>{{ $order->rejected_at ? 'Commande refusée' : 'Commande annulée' }}</strong>
                @if($order->cancellation_reason)<p>{{ $order->cancellation_reason }}</p>@endif
            @else
                <div class="big">✅</div>
                <strong>Commande {{ mb_strtolower($order->status->label()) }}</strong>
                @if($order->accepted_at)
                    <p>Acceptée à {{ $order->accepted_at->format('H:i') }}@if($order->prep_minutes) · préparation ~{{ $order->prep_minutes }} min @endif</p>
                @endif
            @endif
        </div>
    @endif

    <a class="link" href="{{ route('restaurant.orders') }}">Ouvrir le tableau des commandes →</a>
</div>

@if($awaiting)
<script>
    (function () {
        var timer = document.getElementById('timer');
        var since = parseInt(timer.dataset.since, 10) * 1000;
        function tick() {
            var s = Math.max(0, Math.floor((Date.now() - since) / 1000));
            var m = Math.floor(s / 60), r = s % 60;
            timer.textContent = '⏱ Reçue il y a ' + m + ' min ' + (r < 10 ? '0' : '') + r + ' s';
            timer.classList.toggle('late', s >= 120);
        }
        tick();
        setInterval(tick, 1000);

        var rejectForm = document.getElementById('reject-form');
        document.getElementById('show-reject').addEventListener('click', function () {
            rejectForm.hidden = false;
            this.hidden = true;
            rejectForm.scrollIntoView({ behavior: 'smooth' });
        });
        rejectForm.addEventListener('submit', function (e) {
            if (!confirm(rejectForm.querySelector('[data-confirm]').dataset.confirm)) e.preventDefault();
        });
    })();
</script>
@endif
</body>
</html>
