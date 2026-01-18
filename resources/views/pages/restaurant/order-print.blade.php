<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - Commande #{{ $order->reference }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 0.5cm; size: 80mm auto; }
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .restaurant-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .restaurant-info {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .order-info {
            margin: 10px 0;
            padding: 10px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .order-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .order-label {
            font-weight: bold;
        }
        .items {
            margin: 10px 0;
        }
        .item {
            margin: 8px 0;
            padding-bottom: 8px;
            border-bottom: 1px dotted #ccc;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-options {
            font-size: 10px;
            color: #666;
            margin-left: 10px;
            margin-top: 2px;
        }
        .item-instructions {
            font-size: 10px;
            font-style: italic;
            color: #666;
            margin-left: 10px;
            margin-top: 2px;
        }
        .totals {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .total-final {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border: 1px solid #000;
            margin: 5px 0;
            font-weight: bold;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Imprimer</button>

    <div class="header">
        <div class="restaurant-name">{{ $order->restaurant->name }}</div>
        @if($order->restaurant->address)
            <div class="restaurant-info">{{ $order->restaurant->address }}</div>
        @endif
        @if($order->restaurant->phone)
            <div class="restaurant-info">Tel: {{ $order->restaurant->phone }}</div>
        @endif
        @if($order->restaurant->email)
            <div class="restaurant-info">{{ $order->restaurant->email }}</div>
        @endif
    </div>

    <div class="order-info">
        <div class="order-line">
            <span class="order-label">Commande:</span>
            <span>#{{ $order->reference }}</span>
        </div>
        <div class="order-line">
            <span class="order-label">Date:</span>
            <span>{{ $order->created_at->locale('fr')->isoFormat('DD/MM/YYYY à HH:mm') }}</span>
        </div>
        <div class="order-line">
            <span class="order-label">Type:</span>
            <span>{{ $order->type->label() }}</span>
        </div>
        @if($order->table_number)
            <div class="order-line">
                <span class="order-label">Table:</span>
                <span>{{ $order->table_number }}</span>
            </div>
        @endif
        @if($order->delivery_address)
            <div class="order-line">
                <span class="order-label">Adresse:</span>
                <span>{{ $order->delivery_address }}, {{ $order->delivery_city }}</span>
            </div>
        @endif
        <div class="order-line">
            <span class="order-label">Statut:</span>
            <span class="status-badge">{{ $order->status->label() }}</span>
        </div>
    </div>

    <div class="order-info">
        <div class="order-line">
            <span class="order-label">Client:</span>
            <span>{{ $order->customer_name }}</span>
        </div>
        <div class="order-line">
            <span class="order-label">Téléphone:</span>
            <span>{{ $order->customer_phone }}</span>
        </div>
        @if($order->customer_email)
            <div class="order-line">
                <span class="order-label">Email:</span>
                <span>{{ $order->customer_email }}</span>
            </div>
        @endif
    </div>

    <div class="items">
        <div style="font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px;">
            ARTICLES
        </div>
        @foreach($order->items as $item)
            <div class="item">
                <div class="item-header">
                    <span class="item-name">{{ $item->quantity }}x {{ $item->dish_name }}</span>
                    <span>{{ number_format($item->total_price, 0, ',', ' ') }} F</span>
                </div>
                @if($item->selected_options && count($item->selected_options) > 0)
                    <div class="item-options">
                        + {{ collect($item->selected_options)->pluck('name')->join(', ') }}
                    </div>
                @endif
                @if($item->special_instructions)
                    <div class="item-instructions">
                        Note: {{ $item->special_instructions }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="totals">
        <div class="total-line">
            <span>Sous-total:</span>
            <span>{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
        </div>
        @if($order->delivery_fee > 0)
            <div class="total-line">
                <span>Livraison:</span>
                <span>{{ number_format($order->delivery_fee, 0, ',', ' ') }} F</span>
            </div>
        @endif
        @if($order->discount_amount > 0)
            <div class="total-line">
                <span>Réduction:</span>
                <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
            </div>
        @endif
        @if($order->tax_amount > 0)
            <div class="total-line">
                <span>{{ $order->restaurant->tax_name ?? 'Taxe' }}:</span>
                <span>{{ number_format($order->tax_amount, 0, ',', ' ') }} F</span>
            </div>
        @endif
        @if($order->service_fee > 0)
            <div class="total-line">
                <span>Frais de service:</span>
                <span>{{ number_format($order->service_fee, 0, ',', ' ') }} F</span>
            </div>
        @endif
        <div class="total-line total-final">
            <span>TOTAL:</span>
            <span>{{ number_format($order->total, 0, ',', ' ') }} F</span>
        </div>
        @if($order->is_paid)
            <div class="total-line" style="margin-top: 5px;">
                <span style="font-weight: bold;">✓ PAYÉ</span>
                @if($order->payment_method)
                    <span>({{ $order->payment_method }})</span>
                @endif
            </div>
        @else
            <div class="total-line" style="margin-top: 5px;">
                <span style="font-weight: bold; color: #d32f2f;">EN ATTENTE DE PAIEMENT</span>
            </div>
        @endif
    </div>

    @if($order->customer_notes)
        <div style="margin-top: 15px; padding: 10px; border: 1px dashed #000; font-size: 10px;">
            <strong>Note client:</strong><br>
            {{ $order->customer_notes }}
        </div>
    @endif

    <div class="footer">
        <div>Merci de votre visite !</div>
        <div style="margin-top: 5px;">{{ now()->locale('fr')->isoFormat('DD/MM/YYYY HH:mm') }}</div>
    </div>
</body>
</html>

