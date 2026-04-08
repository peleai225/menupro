@extends('emails.layout')

@php
    $count = $ingredients->count();
    $plural = $count > 1 ? 's' : '';
@endphp

@section('title', $count . ' ingredient' . $plural . ' en stock bas')
@section('header', 'Alerte Stock')
@section('subtitle', $restaurant->name)

@section('action_url', route('restaurant.stock.alerts'))
@section('action_text', 'Gerer le stock')

@section('content')
<p style="margin:0 0 16px;font-size:15px;color:#374151;">
    Bonjour <strong>{{ $notifiable->first_name }}</strong>,
</p>

<p style="margin:0 0 24px;font-size:15px;color:#374151;">
    <strong>{{ $count }} ingredient{{ $plural }}</strong> de votre restaurant
    <strong>{{ $restaurant->name }}</strong> necessite{{ $count > 1 ? 'nt' : '' }} un reapprovisionnement.
</p>

<!-- Warning Badge -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:8px;margin-bottom:24px;">
    <tr>
        <td style="padding:12px 20px;text-align:center;">
            <span style="font-size:14px;color:#b45309;font-weight:600;">&#9888;&#65039; {{ $count }} ingredient{{ $plural }} sous le seuil minimum</span>
        </td>
    </tr>
</table>

<!-- Ingredients Table -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:24px;">
    <!-- Table Header -->
    <tr>
        <td style="background-color:#f9fafb;padding:10px 16px;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;">
            Ingredient
        </td>
        <td style="background-color:#f9fafb;padding:10px 16px;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;text-align:center;">
            Stock actuel
        </td>
        <td style="background-color:#f9fafb;padding:10px 16px;font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e5e7eb;text-align:center;">
            Seuil
        </td>
    </tr>
    <!-- Ingredients Rows -->
    @foreach($ingredients->take(8) as $ingredient)
    <tr>
        <td style="padding:12px 16px;font-size:14px;color:#1f2937;font-weight:500;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
            {{ $ingredient->name }}
        </td>
        <td style="padding:12px 16px;font-size:14px;color:#dc2626;font-weight:600;text-align:center;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
            {{ $ingredient->formatted_quantity }}
        </td>
        <td style="padding:12px 16px;font-size:14px;color:#6b7280;text-align:center;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
            {{ $ingredient->min_quantity }} {{ $ingredient->unit->shortLabel() }}
        </td>
    </tr>
    @endforeach
</table>

@if($count > 8)
<p style="margin:0 0 16px;font-size:13px;color:#6b7280;text-align:center;">
    ... et {{ $count - 8 }} autre(s) ingredient(s).
</p>
@endif

<p style="margin:0;font-size:13px;color:#9ca3af;text-align:center;">
    Reapprovisionnez rapidement pour eviter les ruptures de stock.
</p>
@endsection
