{{--
    Choix de l'opérateur Mobile Money exigé par Jeko (paymentMethod). Vrais boutons radio
    name="jeko_operator" : envoyés même si Alpine ne s'est pas chargé. Grandes cibles (≥ 56 px),
    2 colonnes sur téléphone. Variable Alpine optionnelle : jeko_operator (x-model).
--}}
@php
    $operators = [
        'wave'   => ['label' => 'Wave',         'logo' => 'wave.png'],
        'orange' => ['label' => 'Orange Money', 'logo' => 'orange-money.png'],
        'mtn'    => ['label' => 'MTN MoMo',     'logo' => 'mtn-momo.png'],
        'moov'   => ['label' => 'Moov Money',   'logo' => 'moov-money.png'],
    ];
    $selectedOperator = old('jeko_operator', $selected ?? 'wave');
@endphp
<fieldset class="{{ $class ?? '' }}" data-operator-picker>
    <legend class="text-sm font-semibold text-neutral-800 mb-2">Payer avec :</legend>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        @foreach($operators as $op => $info)
            <label class="relative flex items-center gap-2 min-h-[56px] px-3 py-2 rounded-xl border-2 cursor-pointer transition-all bg-white has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 border-neutral-200">
                <input type="radio" name="jeko_operator" value="{{ $op }}" x-model="jeko_operator"
                       class="w-4 h-4 text-primary-600 border-neutral-300 focus:ring-primary-500 shrink-0"
                       @checked($selectedOperator === $op)>
                <img src="{{ asset('images/payments/' . $info['logo']) }}" alt="" class="h-7 w-7 object-contain shrink-0" loading="lazy">
                <span class="text-sm font-semibold text-neutral-800 leading-tight">{{ $info['label'] }}</span>
            </label>
        @endforeach
    </div>
    <p class="text-xs text-neutral-500 mt-2">Vous serez redirigé vers la page sécurisée de Jeko pour valider le paiement sur votre téléphone.</p>
</fieldset>
