@extends('emails.layout')

@section('title', 'Nouvelle commande #' . $order->reference)
@section('header', 'Nouvelle commande !')
@section('subtitle', 'Commande #' . $order->reference)

@section('action_url', route('restaurant.orders.show', $order))
@section('action_text', 'Voir la commande')

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    Bonjour <strong>{{ $notifiable->first_name }}</strong>,
</p>

<p style="margin:0 0 24px;font-size:15px;color:#374151;">
    Vous avez recu une nouvelle commande de <strong>{{ $order->customer_name }}</strong>.
</p>

<!-- Order Summary Card -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;width:140px;">Reference</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;font-weight:600;">#{{ $order->reference }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Client</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;font-weight:600;border-top:1px solid #fed7aa;">{{ $order->customer_name }}</td>
                </tr>
                @if($order->customer_phone)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Telephone</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;border-top:1px solid #fed7aa;">{{ $order->customer_phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Type</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;border-top:1px solid #fed7aa;">{{ $order->type->label() }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Articles</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;border-top:1px solid #fed7aa;">{{ $order->items_count }} article(s)</td>
                </tr>
                <tr>
                    <td style="padding:12px 0 6px;font-size:14px;color:#6b7280;border-top:2px solid #f97316;">Montant total</td>
                    <td style="padding:12px 0 6px;font-size:18px;color:#ea580c;font-weight:700;border-top:2px solid #f97316;">{{ $order->formatted_total }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:13px;color:#9ca3af;text-align:center;">
    Connectez-vous a votre dashboard pour gerer cette commande.
</p>
@endsection
