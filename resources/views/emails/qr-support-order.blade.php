<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouvelle commande supports QR - {{ $order['ref'] }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 620px; margin: 0 auto; padding: 20px; background: #f5f5f5;">

    <div style="background: linear-gradient(135deg, #D45E0C 0%, #b84e0a 100%); padding: 28px 30px; border-radius: 12px 12px 0 0;">
        <div style="color: rgba(255,255,255,0.85); font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; margin-bottom: 6px;">Nouvelle commande</div>
        <h1 style="color: white; margin: 0; font-size: 22px; font-weight: 700;">Supports QR code &middot; {{ $order['ref'] }}</h1>
        <div style="color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 6px;">Recue le {{ $order['date'] }}</div>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e5e5e5; border-top: none;">

        <h2 style="font-size: 14px; color: #D45E0C; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 1px;">Details de la commande</h2>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Format :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    @if($order['format'] === 'support')
                        Support rigide pose sur table
                    @else
                        Autocollant plastifie
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Quantite :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ $order['quantity'] }} unites</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Prix unitaire :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ number_format($order['unit_price'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Sous-total :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ number_format($order['subtotal'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Livraison :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    @if($order['delivery'] == 0)
                        <span style="color: #059669; font-weight: bold;">Offerte</span>
                    @else
                        {{ number_format($order['delivery'], 0, ',', ' ') }} FCFA
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 14px 0 4px; font-weight: bold; font-size: 16px;">Total :</td>
                <td style="padding: 14px 0 4px; font-weight: bold; font-size: 18px; color: #D45E0C;">
                    {{ number_format($order['total'], 0, ',', ' ') }} FCFA
                </td>
            </tr>
        </table>

        <h2 style="font-size: 14px; color: #D45E0C; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 1px;">Coordonnees client</h2>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;">Nom :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ $order['name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Telephone :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    <a href="tel:{{ $order['phone'] }}" style="color: #D45E0C; text-decoration: none;">{{ $order['phone'] }}</a>
                </td>
            </tr>
            @if(!empty($order['email']))
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Email :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    <a href="mailto:{{ $order['email'] }}" style="color: #D45E0C; text-decoration: none;">{{ $order['email'] }}</a>
                </td>
            </tr>
            @endif
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold;">Ville :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ $order['city'] }}</td>
            </tr>
            @if(!empty($order['address']))
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee; font-weight: bold; vertical-align: top;">Adresse :</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #eee;">{{ $order['address'] }}</td>
            </tr>
            @endif
        </table>

        @if(!empty($order['note']))
        <h2 style="font-size: 14px; color: #D45E0C; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 1px;">Note du client</h2>
        <div style="background: #fff7ed; padding: 16px 18px; border-left: 4px solid #D45E0C; border-radius: 4px; margin-bottom: 24px; color: #4a4a4a;">
            {!! nl2br(e($order['note'])) !!}
        </div>
        @endif

        <div style="background: #f9f9f9; padding: 16px 18px; border-radius: 8px; font-size: 13px; color: #555;">
            <strong style="color: #111;">Action requise :</strong> contacter le client sous 24h ouvrees pour confirmer la commande, le format, et organiser la production puis la livraison.
        </div>

        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #eee; font-size: 11px; color: #999; text-align: center;">
            Email genere automatiquement depuis la page d'accueil MenuPro &middot; Reference {{ $order['ref'] }}
        </div>
    </div>
</body>
</html>
