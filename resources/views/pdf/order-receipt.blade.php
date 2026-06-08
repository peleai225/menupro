<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; padding: 15px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .separator { border-top: 1px dashed #aaa; margin: 8px 0; }
        .separator-thick { border-top: 2px solid #333; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        td.right { text-align: right; white-space: nowrap; }
        .header { margin-bottom: 10px; text-align: center; }
        .header h1 { font-size: 18px; margin-bottom: 3px; }
        .header p { font-size: 10px; color: #666; }
        .item-name { font-weight: bold; font-size: 12px; }
        .item-options { font-size: 10px; color: #666; padding-left: 10px; }
        .total-line td { font-size: 16px; font-weight: bold; padding-top: 5px; }
        .footer { margin-top: 12px; font-size: 10px; color: #666; text-align: center; }
        .info td { font-size: 11px; padding: 1px 0; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $restaurant->name }}</h1>
        @if($restaurant->address)
            <p>{{ $restaurant->address }}</p>
        @endif
        @if($restaurant->phone)
            <p>Tel: {{ $restaurant->phone }}</p>
        @endif
    </div>

    <div class="separator-thick"></div>

    {{-- Order Info --}}
    <table class="info">
        <tr>
            <td class="bold">Commande #{{ $order->reference }}</td>
            <td class="right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Client: {{ $order->customer_name }}</td>
            <td class="right">{{ $order->customer_phone }}</td>
        </tr>
        @if($order->type)
            <tr>
                <td>{{ $order->type->label() }}</td>
                @if($order->table_number)
                    <td class="right">Table {{ $order->table_number }}</td>
                @else
                    <td></td>
                @endif
            </tr>
        @endif
    </table>

    <div class="separator"></div>

    {{-- Items --}}
    <table>
        @foreach($order->items as $item)
            <tr>
                <td class="item-name">{{ $item->quantity }}x {{ $item->dish?->name ?? $item->dish_name ?? 'Plat' }}</td>
                <td class="right bold">{{ number_format($item->total_price, 0, ',', ' ') }} F</td>
            </tr>
            @if($item->options && count($item->options) > 0)
                <tr>
                    <td class="item-options" colspan="2">
                        @foreach($item->options as $option)
                            + {{ is_array($option) ? ($option['name'] ?? '') : $option }}<br>
                        @endforeach
                    </td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="separator"></div>

    {{-- Totals --}}
    <table>
        <tr>
            <td>Sous-total</td>
            <td class="right">{{ number_format($order->subtotal, 0, ',', ' ') }} F</td>
        </tr>
        @if($order->delivery_fee > 0)
            <tr>
                <td>Livraison</td>
                <td class="right">{{ number_format($order->delivery_fee, 0, ',', ' ') }} F</td>
            </tr>
        @endif
        @if($order->tax_amount > 0)
            <tr>
                <td>Taxes</td>
                <td class="right">{{ number_format($order->tax_amount, 0, ',', ' ') }} F</td>
            </tr>
        @endif
        @if($order->service_fee > 0)
            <tr>
                <td>Frais de service</td>
                <td class="right">{{ number_format($order->service_fee, 0, ',', ' ') }} F</td>
            </tr>
        @endif
        @if($order->discount_amount > 0)
            <tr>
                <td>Réduction</td>
                <td class="right">-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</td>
            </tr>
        @endif
    </table>

    <div class="separator"></div>

    <table>
        <tr class="total-line">
            <td>TOTAL</td>
            <td class="right">{{ number_format($order->total, 0, ',', ' ') }} F CFA</td>
        </tr>
    </table>

    @if($order->payment_method)
        <table class="info" style="margin-top: 6px;">
            <tr>
                <td>Paiement:</td>
                <td class="right">{{ ucfirst($order->payment_method) }}</td>
            </tr>
        </table>
    @endif

    <div class="separator-thick"></div>

    {{-- Footer --}}
    <div class="footer">
        <p>Merci pour votre commande !</p>
        <p style="margin-top: 5px;">Propulse par MenuPro</p>
    </div>
</body>
</html>
