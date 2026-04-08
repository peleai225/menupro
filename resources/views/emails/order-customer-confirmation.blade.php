@extends('emails.layout')

@section('title', 'Commande confirmee #' . $order->reference)
@section('header', 'Commande confirmee !')
@section('subtitle', $restaurant->name)

@if($trackingUrl)
@section('action_url', $trackingUrl)
@section('action_text', 'Suivre ma commande')
@endif

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    Bonjour <strong>{{ $order->customer_name }}</strong>,
</p>

<p style="margin:0 0 24px;font-size:15px;color:#374151;">
    Merci pour votre commande chez <strong>{{ $restaurant->name }}</strong> ! Voici le recapitulatif :
</p>

<!-- Order Info Card -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff7ed;border:1px solid #fed7aa;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;width:140px;">Reference</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;font-weight:600;">#{{ $order->reference }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Type</td>
                    <td style="padding:6px 0;font-size:14px;color:#1f2937;border-top:1px solid #fed7aa;">{{ $order->type->label() }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#6b7280;border-top:1px solid #fed7aa;">Statut</td>
                    <td style="padding:6px 0;border-top:1px solid #fed7aa;">
                        <span style="display:inline-block;padding:2px 10px;background-color:#f0fdf4;color:#15803d;font-size:12px;font-weight:600;border-radius:12px;">
                            Confirmee
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Order Items -->
@if($order->items && $order->items->count() > 0)
<p style="margin:0 0 12px;font-size:14px;color:#374151;font-weight:600;">Articles commandes :</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:16px;">
    @foreach($order->items as $item)
    <tr>
        <td style="padding:10px 16px;font-size:14px;color:#1f2937;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
            {{ $item->quantity }}x {{ $item->name }}
        </td>
        <td style="padding:10px 16px;font-size:14px;color:#1f2937;font-weight:600;text-align:right;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
            {{ number_format($item->subtotal, 0, ',', ' ') }} F
        </td>
    </tr>
    @endforeach
</table>
@endif

<!-- Total -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:16px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="font-size:15px;color:#374151;font-weight:600;">Total</td>
                    <td style="font-size:20px;color:#ea580c;font-weight:700;text-align:right;">{{ $order->formatted_total }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p style="margin:0;font-size:13px;color:#9ca3af;text-align:center;">
    Pour toute question, contactez directement le restaurant.
</p>
@endsection
