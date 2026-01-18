<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport - {{ $restaurant->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1c1917;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
            border-collapse: collapse;
        }
        .info table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .info table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .total {
            font-weight: bold;
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurant->name }}</h1>
        <p>Rapport {{ ucfirst($reportType) }}</p>
        <p>Période : {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
        <p>Généré le : {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    @if($reportType === 'sales')
        <h2>Ventes par jour</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Commandes</th>
                    <th>Revenus (FCFA)</th>
                    <th>Moyenne (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['sales_by_day'] ?? [] as $day => $stats)
                    <tr>
                        <td>{{ $day }}</td>
                        <td>{{ $stats['count'] ?? 0 }}</td>
                        <td>{{ number_format(($stats['revenue'] ?? 0) / 100, 0, ',', ' ') }}</td>
                        <td>{{ number_format(($stats['average'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($reportType === 'dishes')
        <h2>Top plats</h2>
        <table>
            <thead>
                <tr>
                    <th>Plat</th>
                    <th>Quantité vendue</th>
                    <th>Revenus (FCFA)</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['top_dishes'] ?? [] as $dish)
                    <tr>
                        <td>{{ $dish['name'] ?? '' }}</td>
                        <td>{{ $dish['quantity'] ?? 0 }}</td>
                        <td>{{ number_format(($dish['revenue'] ?? 0) / 100, 0, ',', ' ') }}</td>
                        <td>{{ number_format(($dish['percentage'] ?? 0), 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($reportType === 'customers')
        <h2>Top clients</h2>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Commandes</th>
                    <th>Total dépensé (FCFA)</th>
                    <th>Dernière commande</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['top_customers'] ?? [] as $customer)
                    <tr>
                        <td>{{ $customer['name'] ?? '' }}</td>
                        <td>{{ $customer['email'] ?? '' }}</td>
                        <td>{{ $customer['orders_count'] ?? 0 }}</td>
                        <td>{{ number_format(($customer['total_spent'] ?? 0) / 100, 0, ',', ' ') }}</td>
                        <td>{{ $customer['last_order'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($reportType === 'financial')
        <h2>Résumé financier</h2>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Montant (FCFA)</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sous-total</td>
                    <td>{{ number_format(($reportData['total_subtotal'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Frais de livraison</td>
                    <td>{{ number_format(($reportData['total_delivery_fees'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Réductions</td>
                    <td>-{{ number_format(($reportData['total_discounts'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
                <tr class="total">
                    <td>TOTAL REVENUS</td>
                    <td>{{ number_format(($reportData['total_revenue'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>

        <h2 style="margin-top: 30px;">Revenus par méthode de paiement</h2>
        <table>
            <thead>
                <tr>
                    <th>Méthode</th>
                    <th>Montant (FCFA)</th>
                    <th>Pourcentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['revenue_by_payment'] ?? [] as $method => $amount)
                    @php
                        $total = $reportData['total_revenue'] ?? 1;
                        $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ ucfirst($method) }}</td>
                        <td>{{ number_format($amount / 100, 0, ',', ' ') }}</td>
                        <td>{{ number_format($percentage, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Rapport généré par MenuPro - {{ config('app.name') }}</p>
    </div>
</body>
</html>

