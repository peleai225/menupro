@extends('emails.layout')

@section('title', 'Restaurant valide !')
@section('header', 'Felicitations !')
@section('subtitle', 'Votre restaurant est en ligne')

@section('action_url', route('restaurant.dashboard'))
@section('action_text', 'Acceder a mon dashboard')

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    Bonjour <strong>{{ $notifiable->first_name }}</strong>,
</p>

<p style="margin:0 0 24px;font-size:15px;color:#374151;">
    Votre restaurant <strong>{{ $restaurant->name }}</strong> a ete valide et est maintenant en ligne sur MenuPro !
</p>

<!-- Success Badge -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;">
                <tr>
                    <td style="padding:16px 24px;text-align:center;">
                        <span style="font-size:28px;">&#9989;</span>
                        <p style="margin:8px 0 0;font-size:15px;color:#15803d;font-weight:600;">Restaurant actif</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Public URL -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 8px;font-size:13px;color:#3b82f6;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">
                Votre lien public
            </p>
            <a href="{{ $restaurant->public_url }}" target="_blank" style="font-size:15px;color:#1d4ed8;word-break:break-all;">
                {{ $restaurant->public_url }}
            </a>
        </td>
    </tr>
</table>

<!-- Next Steps -->
<p style="margin:0 0 12px;font-size:15px;color:#374151;font-weight:600;">
    Prochaines etapes :
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding:8px 0;font-size:14px;color:#374151;">
            <span style="color:#f97316;font-weight:700;margin-right:8px;">1.</span>
            Ajoutez vos plats et categories dans le menu
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0;font-size:14px;color:#374151;border-top:1px solid #f3f4f6;">
            <span style="color:#f97316;font-weight:700;margin-right:8px;">2.</span>
            Configurez vos modes de paiement
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0;font-size:14px;color:#374151;border-top:1px solid #f3f4f6;">
            <span style="color:#f97316;font-weight:700;margin-right:8px;">3.</span>
            Partagez votre lien sur vos reseaux sociaux
        </td>
    </tr>
</table>
@endsection
