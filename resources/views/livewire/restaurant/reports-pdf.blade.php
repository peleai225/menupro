<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport — {{ $restaurant->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }

        /* Header */
        .header { padding: 20px 24px 16px; border-bottom: 3px solid #D45E0C; margin-bottom: 20px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .restaurant-name { font-size: 20px; font-weight: 700; color: #161616; }
        .report-title { font-size: 13px; color: #D45E0C; font-weight: 600; margin-top: 2px; }
        .header-meta { text-align: right; font-size: 10px; color: #6b7280; }
        .header-meta p { margin-bottom: 2px; }

        /* KPI Cards */
        .kpi-grid { display: table; width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 8px; }
        .kpi-card { display: table-cell; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; text-align: center; }
        .kpi-value { font-size: 20px; font-weight: 700; color: #D45E0C; }
        .kpi-label { font-size: 9px; color: #6b7280; margin-top: 3px; text-transform: uppercase; letter-spacing: .5px; }

        /* Section titles */
        .section-title { font-size: 13px; font-weight: 700; color: #161616; margin: 18px 0 8px; padding-bottom: 5px; border-bottom: 2px solid #D45E0C; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10.5px; }
        thead th { background: #D45E0C; color: #fff; padding: 8px 10px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        .total-row td { background: #fef3c7 !important; font-weight: 700; border-top: 2px solid #D45E0C; }
        .num { text-align: right; }
        .center { text-align: center; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }

        /* Footer */
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }

        /* Info box */
        .info-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 10px; color: #92400e; }
    </style>
</head>
<body>

    {{-- ─── HEADER ─── --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="restaurant-name">{{ $restaurant->name }}</div>
                <div class="report-title">
                    @php
                        $titles = [
                            'sales'     => 'Rapport de Ventes',
                            'dishes'    => 'Rapport Plats',
                            'customers' => 'Rapport Clients',
                            'financial' => 'Rapport Financier',
                            'waiters'   => 'Rapport Serveurs',
                            'daily'     => 'Bilan Journalier',
                        ];
                    @endphp
                    {{ $titles[$reportType] ?? 'Rapport' }}
                </div>
            </div>
            <div class="header-meta">
                <p><strong>Période :</strong> {{ $startDate->format('d/m/Y') }} → {{ $endDate->format('d/m/Y') }}</p>
                <p><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
                @if($restaurant->city)
                <p><strong>Ville :</strong> {{ $restaurant->city }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════
         RAPPORT VENTES
    ════════════════════════════════════ --}}
    @if($reportType === 'sales')

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($reportData['total_orders'] ?? 0) }}</div>
                    <div class="kpi-label">Commandes</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($reportData['total_revenue'] ?? 0, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">CA Total</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($reportData['average_order'] ?? 0, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">Panier moyen</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($reportData['total_revenue_net'] ?? 0, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">CA Net</div>
                </td>
            </tr>
        </table>

        {{-- Ventes par jour --}}
        <div class="section-title">Évolution journalière</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="num">Commandes</th>
                    <th class="num">CA Brut (FCFA)</th>
                    <th class="num">CA Net (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @php $totOrders = 0; $totBrut = 0; $totNet = 0; @endphp
                @foreach($reportData['sales_by_day'] ?? [] as $day)
                    @php
                        $totOrders += $day['orders'] ?? 0;
                        $totBrut   += $day['revenue_brut'] ?? 0;
                        $totNet    += $day['revenue_net']  ?? 0;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day['date'] ?? '')->format('d/m/Y') }}</td>
                        <td class="num">{{ $day['orders'] ?? 0 }}</td>
                        <td class="num">{{ number_format($day['revenue_brut'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="num">{{ number_format($day['revenue_net']  ?? 0, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="num">{{ $totOrders }}</td>
                    <td class="num">{{ number_format($totBrut, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($totNet,  0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Ventes par type --}}
        @if(!empty($reportData['sales_by_type']))
        <div class="section-title">Répartition par type</div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th class="num">Commandes</th>
                    <th class="num">CA (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['sales_by_type'] as $type)
                @php
                    $labels = ['dine_in' => 'Sur place', 'takeaway' => 'À emporter', 'delivery' => 'Livraison'];
                @endphp
                <tr>
                    <td>{{ $labels[$type['type'] ?? ''] ?? ucfirst($type['type'] ?? '') }}</td>
                    <td class="num">{{ $type['count'] ?? 0 }}</td>
                    <td class="num">{{ number_format($type['revenue_brut'] ?? $type['revenue'] ?? 0, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @endif

    {{-- ════════════════════════════════════
         RAPPORT PLATS
    ════════════════════════════════════ --}}
    @if($reportType === 'dishes')

        <div class="section-title">Top plats les plus vendus</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Plat</th>
                    <th class="num">Quantité</th>
                    <th class="num">CA (FCFA)</th>
                    <th class="num">Prix moyen</th>
                </tr>
            </thead>
            <tbody>
                @php $totSold = 0; $totRev = 0; @endphp
                @foreach($reportData['top_dishes'] ?? [] as $i => $dish)
                    @php
                        $totSold += $dish['total_sold'] ?? 0;
                        $totRev  += $dish['total_revenue'] ?? 0;
                        $avg = ($dish['total_sold'] ?? 0) > 0
                            ? ($dish['total_revenue'] ?? 0) / $dish['total_sold']
                            : ($dish['avg_price'] ?? 0);
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $dish['name'] ?? '' }}</td>
                        <td class="num">{{ $dish['total_sold'] ?? 0 }}</td>
                        <td class="num">{{ number_format($dish['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="num">{{ number_format($avg, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td></td>
                    <td>TOTAL</td>
                    <td class="num">{{ $totSold }}</td>
                    <td class="num">{{ number_format($totRev, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        {{-- Ventes par catégorie --}}
        @if(!empty($reportData['dishes_by_category']))
        <div class="section-title">Ventes par catégorie</div>
        <table>
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th class="num">Plats vendus</th>
                    <th class="num">CA (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['dishes_by_category'] as $cat)
                <tr>
                    <td>{{ $cat['name'] ?? '' }}</td>
                    <td class="num">{{ $cat['total_sold'] ?? 0 }}</td>
                    <td class="num">{{ number_format($cat['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

    @endif

    {{-- ════════════════════════════════════
         RAPPORT CLIENTS
    ════════════════════════════════════ --}}
    @if($reportType === 'customers')

        <div class="info-box">
            Clients uniques sur la période : <strong>{{ $reportData['total_customers'] ?? 0 }}</strong>
        </div>

        <div class="section-title">Top clients</div>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th class="num">Commandes</th>
                    <th class="num">Total dépensé (FCFA)</th>
                    <th class="num">Panier moyen (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['top_customers'] ?? [] as $c)
                    @php
                        $avg = ($c['orders_count'] ?? 0) > 0
                            ? ($c['total_spent'] ?? 0) / $c['orders_count']
                            : 0;
                    @endphp
                    <tr>
                        <td>{{ $c['customer_name']  ?? '' }}</td>
                        <td>{{ $c['customer_phone'] ?? '' }}</td>
                        <td class="num">{{ $c['orders_count'] ?? 0 }}</td>
                        <td class="num">{{ number_format($c['total_spent'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="num">{{ number_format($avg, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    {{-- ════════════════════════════════════
         RAPPORT FINANCIER
    ════════════════════════════════════ --}}
    @if($reportType === 'financial')

        @php
            $brut     = $reportData['total_revenue_brut'] ?? $reportData['total_revenue'] ?? 0;
            $net      = $reportData['total_revenue_net']  ?? 0;
            $cash     = $reportData['cash_total']         ?? 0;
            $mobile   = $reportData['mobile_total']       ?? 0;
            $discount = $reportData['total_discounts']    ?? 0;
            $delivery = $reportData['total_delivery_fees']?? 0;
            $comm     = $reportData['total_commission']   ?? 0;
        @endphp

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($brut, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">CA Brut</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value" style="color:#16a34a">{{ number_format($cash, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">Espèces</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value" style="color:#2563eb">{{ number_format($mobile, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">Mobile Money</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($net, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">CA Net</div>
                </td>
            </tr>
        </table>

        {{-- Résumé --}}
        <div class="section-title">Résumé financier</div>
        <table>
            <thead><tr><th>Catégorie</th><th class="num">Montant (FCFA)</th><th class="num">Note</th></tr></thead>
            <tbody>
                <tr><td>CA Brut</td><td class="num">{{ number_format($brut, 0, ',', ' ') }}</td><td></td></tr>
                <tr><td>Sous-total HT</td><td class="num">{{ number_format($reportData['total_subtotal'] ?? 0, 0, ',', ' ') }}</td><td></td></tr>
                <tr><td>Frais de livraison</td><td class="num">{{ number_format($delivery, 0, ',', ' ') }}</td><td></td></tr>
                <tr><td>Réductions accordées</td><td class="num" style="color:#dc2626">-{{ number_format($discount, 0, ',', ' ') }}</td><td></td></tr>
                <tr><td>Commissions plateforme</td><td class="num" style="color:#dc2626">-{{ number_format($comm, 0, ',', ' ') }}</td><td></td></tr>
                <tr class="total-row"><td>CA Net</td><td class="num">{{ number_format($net, 0, ',', ' ') }}</td><td></td></tr>
            </tbody>
        </table>

        {{-- Répartition paiements --}}
        @php
            $payments = $reportData['by_payment_detailed'] ?? $reportData['revenue_by_payment'] ?? [];
        @endphp
        @if(!empty($payments))
        <div class="section-title">Répartition par moyen de paiement</div>
        <table>
            <thead><tr><th>Moyen de paiement</th><th class="num">Montant (FCFA)</th><th class="num">Transactions</th><th class="num">%</th></tr></thead>
            <tbody>
                @foreach($payments as $p)
                    @php
                        $lbl    = $p['label'] ?? ucfirst($p['payment_method'] ?? ($p['method'] ?? '—'));
                        $amt    = $p['total_amount'] ?? $p['revenue_brut'] ?? $p['revenue'] ?? 0;
                        $cnt    = $p['orders_count'] ?? $p['count'] ?? 0;
                        $pct    = $brut > 0 ? round($amt / $brut * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>{{ $lbl }}</td>
                        <td class="num">{{ number_format($amt, 0, ',', ' ') }}</td>
                        <td class="num">{{ $cnt }}</td>
                        <td class="num">{{ $pct }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- Comparaison période --}}
        @if(!empty($reportData['vs_previous']))
        @php $vs = $reportData['vs_previous']; $sign = ($vs['change_pct'] ?? 0) >= 0 ? '+' : ''; @endphp
        <div class="section-title">Comparaison avec la période précédente</div>
        <table>
            <thead><tr><th>Indicateur</th><th class="num">Période actuelle</th><th class="num">Période précédente</th><th class="num">Évolution</th></tr></thead>
            <tbody>
                <tr>
                    <td>Chiffre d'affaires</td>
                    <td class="num">{{ number_format($brut, 0, ',', ' ') }} F</td>
                    <td class="num">{{ number_format($vs['revenue'] ?? 0, 0, ',', ' ') }} F</td>
                    <td class="num" style="color:{{ ($vs['change_pct'] ?? 0) >= 0 ? '#16a34a' : '#dc2626' }}">
                        {{ $sign }}{{ $vs['change_pct'] ?? 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>
        @endif

    @endif

    {{-- ════════════════════════════════════
         RAPPORT SERVEURS
    ════════════════════════════════════ --}}
    @if($reportType === 'waiters')

        <div class="section-title">Performance par serveur</div>
        <table>
            <thead>
                <tr>
                    <th>Serveur</th>
                    <th class="num">Commandes</th>
                    <th class="num">CA (FCFA)</th>
                    <th class="num">Ticket moyen</th>
                    <th>Espace</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['rows'] ?? $reportData['waiters'] ?? [] as $w)
                <tr>
                    <td>{{ $w['waiter_name'] ?? ($w['name'] ?? '') }}</td>
                    <td class="num">{{ $w['orders_count'] ?? 0 }}</td>
                    <td class="num">{{ number_format($w['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($w['avg_order'] ?? 0, 0, ',', ' ') }}</td>
                    <td>{{ $w['primary_space'] ?? ($w['space'] ?? '') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="num">{{ $reportData['total_orders'] ?? 0 }}</td>
                    <td class="num">{{ number_format($reportData['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

    @endif

    {{-- ════════════════════════════════════
         BILAN JOURNALIER
    ════════════════════════════════════ --}}
    @if($reportType === 'daily')

        @php
            $cancelled = $reportData['cancelled_count'] ?? 0;
            $cashTot   = $reportData['cash_total']      ?? 0;
            $mobileTot = $reportData['mobile_total']    ?? 0;
        @endphp

        {{-- KPIs --}}
        <table class="kpi-grid">
            <tr>
                <td class="kpi-card">
                    <div class="kpi-value">{{ number_format($reportData['total_revenue'] ?? 0, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">CA du jour</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value">{{ $reportData['total_orders'] ?? 0 }}</div>
                    <div class="kpi-label">Commandes</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value" style="color:#16a34a">{{ number_format($cashTot, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">Espèces à encaisser</div>
                </td>
                <td class="kpi-card">
                    <div class="kpi-value" style="color:#2563eb">{{ number_format($mobileTot, 0, ',', ' ') }} F</div>
                    <div class="kpi-label">Mobile Money</div>
                </td>
            </tr>
        </table>

        @if($cancelled > 0)
        <div class="info-box" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b;">
            ⚠ {{ $cancelled }} commande(s) annulée(s) — {{ number_format($reportData['cancelled_lost'] ?? 0, 0, ',', ' ') }} FCFA non encaissés
        </div>
        @endif

        <div class="section-title">Détail par heure de caisse</div>
        <table>
            <thead>
                <tr>
                    <th>Tranche</th>
                    <th class="num">Commandes</th>
                    <th class="num">CA Total (FCFA)</th>
                    <th class="num">Espèces</th>
                    <th class="num">Mobile Money</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['by_hour'] ?? [] as $row)
                <tr>
                    <td>{{ $row['hour_label'] ?? sprintf('%02dh–%02dh', $row['hour'] ?? 0, ($row['hour'] ?? 0)+1) }}</td>
                    <td class="num">{{ $row['orders_count'] ?? 0 }}</td>
                    <td class="num">{{ number_format($row['total_amount']  ?? 0, 0, ',', ' ') }}</td>
                    <td class="num" style="color:#16a34a">{{ number_format($row['cash_amount']   ?? 0, 0, ',', ' ') }}</td>
                    <td class="num" style="color:#2563eb">{{ number_format($row['mobile_amount'] ?? 0, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="num">{{ $reportData['total_orders'] ?? 0 }}</td>
                    <td class="num">{{ number_format($reportData['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($cashTot,   0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($mobileTot, 0, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>

    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <p>Rapport généré par <strong>MenuPro</strong> · {{ config('app.name') }} · {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

</body>
</html>
