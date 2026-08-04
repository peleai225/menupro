# Rapports Détaillés — Bilan Journalier + Financier + KPI Dashboard

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un rapport journalier (bilan de caisse par heure), enrichir le rapport financier (répartition espèces vs mobile money, comparaison période précédente), et ajouter des KPI temps réel sur le dashboard.

**Architecture:** Tout s'appuie sur `RevenueCalculator` (service existant) et la page `Reports` (Livewire existant). Aucune migration ni nouvelle table — les données `paid_at`, `payment_method`, `payment_metadata` existent déjà. On ajoute un nouveau type de rapport `daily` dans `Reports.php` + une méthode `cancellationStats()` dans `RevenueCalculator` + un widget KPI dans `Dashboard.php`.

**Tech Stack:** Laravel 11, Livewire 3, Tailwind CSS, Chart.js 4.5.1, PHP 8.3, `RevenueCalculator` service existant dans `app/Services/RevenueCalculator.php`.

## Global Constraints

- Ne jamais inclure les commandes avec `status` = `cancelled`, `refunded`, `draft` dans les totaux
- Utiliser `paid_at` (pas `created_at`) pour les calculs horaires — c'est l'heure réelle d'encaissement
- `payment_method` = `'cash'` ou `'cash_on_delivery'` = espèces physiques à encaisser
- `payment_method` = `'jeko'` ou `'wave'` = mobile money (déjà sur les comptes)
- Tous les montants en entiers (FCFA, pas de décimales)
- Suivre le pattern `#[Computed]` pour les nouvelles méthodes dans Dashboard.php
- Tous les blocs `@if($reportType === 'xxx')` dans la vue reports.blade.php sont au même niveau
- Chart.js est déjà chargé globalement — pas besoin de le réimporter

---

## Fichiers modifiés/créés

| Fichier | Action | Rôle |
|---------|--------|------|
| `app/Services/RevenueCalculator.php` | Modifier | Ajouter `cancellationStats()`, `revenueByPaymentMethodDetailed()` |
| `app/Livewire/Restaurant/Reports.php` | Modifier | Ajouter type `daily`, méthode `getDailyReport()`, enrichir `getFinancialReport()` |
| `app/Livewire/Restaurant/Dashboard.php` | Modifier | Ajouter KPI `todayVsYesterday()` computed |
| `resources/views/livewire/restaurant/reports.blade.php` | Modifier | Ajouter section rapport journalier, enrichir section financier |
| `resources/views/livewire/restaurant/dashboard.blade.php` | Modifier | Ajouter widget KPI sous les stats existantes |

---

## Task 1 : RevenueCalculator — nouvelles méthodes

**Files:**
- Modify: `app/Services/RevenueCalculator.php:83-138`

**Interfaces:**
- Consumes: Rien de nouveau — utilise `$this->baseQuery()` existant
- Produces:
  - `cancellationStats(): array` — retourne `['count' => int, 'total_lost' => int]`
  - `revenueByPaymentMethodDetailed(): Collection` — retourne items avec `['method' => string, 'label' => string, 'is_cash' => bool, 'total_amount' => int, 'orders_count' => int]`
  - `revenueByHourWithPayment(): Collection` — retourne items avec `['hour' => int, 'total_amount' => int, 'orders_count' => int, 'cash_amount' => int, 'mobile_amount' => int, 'cancelled_count' => int]`

- [ ] **Step 1 : Ouvrir `app/Services/RevenueCalculator.php`** et repérer la fin de la méthode `revenueByHour()` (ligne ~98).

- [ ] **Step 2 : Ajouter `cancellationStats()` après `revenueByHour()`**

```php
public function cancellationStats(): array
{
    $cancelled = Order::where('restaurant_id', $this->restaurantId)
        ->whereBetween('created_at', [$this->from, $this->to])
        ->where('status', \App\Enums\OrderStatus::CANCELLED)
        ->selectRaw('COUNT(*) as count, SUM(total) as total_lost')
        ->first();

    return [
        'count'      => (int) ($cancelled->count ?? 0),
        'total_lost' => (int) ($cancelled->total_lost ?? 0),
    ];
}
```

- [ ] **Step 3 : Ajouter `revenueByPaymentMethodDetailed()` juste après**

```php
public function revenueByPaymentMethodDetailed(): Collection
{
    $rows = $this->baseQuery()
        ->selectRaw('payment_method, SUM(total) as total_amount, COUNT(*) as orders_count')
        ->groupBy('payment_method')
        ->get();

    $cashMethods  = ['cash', 'cash_on_delivery', 'on_site', null];
    $labels = [
        'cash'             => 'Espèces',
        'cash_on_delivery' => 'Espèces (livraison)',
        'on_site'          => 'Sur place',
        'wave'             => 'Wave',
        'jeko'             => 'Jeko / Mobile Money',
        'orange'           => 'Orange Money',
        'mtn'              => 'MTN MoMo',
        'moov'             => 'Moov Money',
    ];

    return $rows->map(function ($row) use ($cashMethods, $labels) {
        $method = $row->payment_method;
        return [
            'method'       => $method ?? 'inconnu',
            'label'        => $labels[$method] ?? ucfirst($method ?? 'Inconnu'),
            'is_cash'      => in_array($method, $cashMethods),
            'total_amount' => (int) $row->total_amount,
            'orders_count' => (int) $row->orders_count,
        ];
    });
}
```

- [ ] **Step 4 : Ajouter `revenueByHourWithPayment()` juste après**

```php
public function revenueByHourWithPayment(): Collection
{
    $cashMethods = ['cash', 'cash_on_delivery', 'on_site'];

    return $this->baseQuery()
        ->selectRaw("
            HOUR(paid_at) as hour,
            COUNT(*) as orders_count,
            SUM(total) as total_amount,
            SUM(CASE WHEN payment_method IN ('cash','cash_on_delivery','on_site') OR payment_method IS NULL
                THEN total ELSE 0 END) as cash_amount,
            SUM(CASE WHEN payment_method NOT IN ('cash','cash_on_delivery','on_site') AND payment_method IS NOT NULL
                THEN total ELSE 0 END) as mobile_amount
        ")
        ->groupByRaw('HOUR(paid_at)')
        ->orderByRaw('HOUR(paid_at)')
        ->get()
        ->map(fn($row) => [
            'hour'          => (int) $row->hour,
            'hour_label'    => sprintf('%02dh-%02dh', $row->hour, $row->hour + 1),
            'orders_count'  => (int) $row->orders_count,
            'total_amount'  => (int) $row->total_amount,
            'cash_amount'   => (int) $row->cash_amount,
            'mobile_amount' => (int) $row->mobile_amount,
        ]);
}
```

- [ ] **Step 5 : Vérifier la syntaxe PHP**

```bash
php -l app/Services/RevenueCalculator.php
```
Expected: `No syntax errors detected`

- [ ] **Step 6 : Commit**

```bash
git add app/Services/RevenueCalculator.php
git commit -m "feat(reports): add cancellationStats, revenueByHourWithPayment, revenueByPaymentMethodDetailed to RevenueCalculator"
```

---

## Task 2 : Reports.php — rapport journalier `getDailyReport()`

**Files:**
- Modify: `app/Livewire/Restaurant/Reports.php`

**Interfaces:**
- Consumes: `RevenueCalculator::for($restaurantId, $start, $end)` + les 3 nouvelles méthodes de Task 1
- Produces: nouvelle méthode `getDailyReport(int $restaurantId, $startDate, $endDate): array` retournant :
```php
[
  'date'            => 'YYYY-MM-DD',
  'total_revenue'   => int,
  'total_orders'    => int,
  'average_ticket'  => int,
  'cash_total'      => int,
  'mobile_total'    => int,
  'cancelled_count' => int,
  'cancelled_lost'  => int,
  'peak_hour'       => string,   // ex: "12h-13h"
  'by_hour'         => array,    // tableau d'items revenueByHourWithPayment
  'by_payment'      => array,    // tableau d'items revenueByPaymentMethodDetailed
]
```

- [ ] **Step 1 : Ajouter l'import de `RevenueCalculator` en haut de `Reports.php`** si absent

```php
use App\Services\RevenueCalculator;
```
Vérifier ligne ~1-15 du fichier. Si déjà présent, ne pas dupliquer.

- [ ] **Step 2 : Ajouter `'daily'` dans le `match` de `getReportData()` (ligne ~75)**

```php
$data = match ($this->reportType) {
    'sales'     => $this->getSalesReport($restaurant->id, $startDate, $endDate),
    'dishes'    => $this->getDishesReport($restaurant->id, $startDate, $endDate),
    'customers' => $this->getCustomersReport($restaurant->id, $startDate, $endDate),
    'financial' => $this->getFinancialReport($restaurant->id, $startDate, $endDate),
    'waiters'   => $this->getWaitersReport($restaurant->id, $startDate, $endDate),
    'daily'     => $this->getDailyReport($restaurant->id, $startDate, $endDate),
    default     => [],
};
```

- [ ] **Step 3 : Ajouter la méthode `getDailyReport()` avant `render()`**

```php
protected function getDailyReport(int $restaurantId, $startDate, $endDate): array
{
    $calc = RevenueCalculator::for($restaurantId, $startDate, $endDate);

    $byHour      = $calc->revenueByHourWithPayment();
    $byPayment   = $calc->revenueByPaymentMethodDetailed();
    $cancelled   = $calc->cancellationStats();

    $cashTotal   = $byPayment->where('is_cash', true)->sum('total_amount');
    $mobileTotal = $byPayment->where('is_cash', false)->sum('total_amount');

    $peakHourRow = $byHour->sortByDesc('orders_count')->first();
    $peakHour    = $peakHourRow ? $peakHourRow['hour_label'] : '—';

    return [
        'date'            => $startDate->toDateString(),
        'total_revenue'   => $calc->grossRevenue(),
        'total_orders'    => $calc->validOrdersCount(),
        'average_ticket'  => $calc->averageTicket(),
        'cash_total'      => (int) $cashTotal,
        'mobile_total'    => (int) $mobileTotal,
        'cancelled_count' => $cancelled['count'],
        'cancelled_lost'  => $cancelled['total_lost'],
        'peak_hour'       => $peakHour,
        'by_hour'         => $byHour->values()->toArray(),
        'by_payment'      => $byPayment->values()->toArray(),
    ];
}
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Livewire/Restaurant/Reports.php
```
Expected: `No syntax errors detected`

- [ ] **Step 5 : Commit**

```bash
git add app/Livewire/Restaurant/Reports.php
git commit -m "feat(reports): add getDailyReport() with hourly breakdown and payment split"
```

---

## Task 3 : Reports.php — enrichir `getFinancialReport()`

**Files:**
- Modify: `app/Livewire/Restaurant/Reports.php:338-421`

**Interfaces:**
- Consumes: `RevenueCalculator::revenueByPaymentMethodDetailed()` de Task 1
- Produces: `getFinancialReport()` enrichi avec :
  - `cash_total`: int — total espèces physiques à encaisser
  - `mobile_total`: int — total mobile money
  - `by_payment_detailed`: array — détail par moyen de paiement avec `is_cash`
  - `vs_previous`: array — `['revenue' => int, 'orders' => int, 'change_pct' => int]` période précédente

- [ ] **Step 1 : Modifier `getFinancialReport()` pour utiliser `RevenueCalculator`**

Remplacer le corps de la méthode par :

```php
protected function getFinancialReport(int $restaurantId, $startDate, $endDate): array
{
    $calc = RevenueCalculator::for($restaurantId, $startDate, $endDate);

    // Période précédente de même durée
    $duration  = $startDate->diffInDays($endDate);
    $prevEnd   = $startDate->copy()->subDay()->endOfDay();
    $prevStart = $prevEnd->copy()->subDays($duration)->startOfDay();
    $calcPrev  = RevenueCalculator::for($restaurantId, $prevStart, $prevEnd);

    $byPayment = $calc->revenueByPaymentMethodDetailed();
    $cancelled = $calc->cancellationStats();

    $prevRevenue = $calcPrev->grossRevenue();
    $curRevenue  = $calc->grossRevenue();
    $changePct   = $prevRevenue > 0
        ? (int) round((($curRevenue - $prevRevenue) / $prevRevenue) * 100)
        : ($curRevenue > 0 ? 100 : 0);

    // Daily revenue trend (inchangé)
    $dailyRevenue = Order::where('restaurant_id', $restaurantId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->whereNotNull('paid_at')
        ->validForReporting()
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue_brut'),
            DB::raw('SUM(subtotal) as subtotal'),
            DB::raw('SUM(delivery_fee) as delivery_fees'),
            DB::raw('SUM(discount_amount) as discounts'),
            DB::raw('SUM(COALESCE(platform_commission, 0)) as commission'),
            DB::raw('SUM(total - COALESCE(platform_commission, 0) - COALESCE(delivery_fee, 0)) as revenue_net')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(fn($item) => [
            'date'         => $item->date,
            'revenue_brut' => (float) $item->revenue_brut,
            'revenue_net'  => (float) $item->revenue_net,
            'subtotal'     => (float) $item->subtotal,
            'delivery_fees'=> (float) $item->delivery_fees,
            'discounts'    => (float) $item->discounts,
            'commission'   => (float) $item->commission,
        ])
        ->values()
        ->toArray();

    return [
        'total_revenue_brut'   => $curRevenue,
        'total_revenue_net'    => $calc->netRevenue(),
        'total_revenue'        => $curRevenue,
        'total_commission'     => $calc->totalCommissions(),
        'total_delivery_fees'  => (int) Order::where('restaurant_id', $restaurantId)->whereBetween('created_at', [$startDate, $endDate])->whereNotNull('paid_at')->validForReporting()->sum('delivery_fee'),
        'total_discounts'      => (int) Order::where('restaurant_id', $restaurantId)->whereBetween('created_at', [$startDate, $endDate])->whereNotNull('paid_at')->validForReporting()->sum('discount_amount'),
        'total_subtotal'       => (int) Order::where('restaurant_id', $restaurantId)->whereBetween('created_at', [$startDate, $endDate])->whereNotNull('paid_at')->validForReporting()->sum('subtotal'),
        'cash_total'           => (int) $byPayment->where('is_cash', true)->sum('total_amount'),
        'mobile_total'         => (int) $byPayment->where('is_cash', false)->sum('total_amount'),
        'cancelled_count'      => $cancelled['count'],
        'cancelled_lost'       => $cancelled['total_lost'],
        'by_payment_detailed'  => $byPayment->values()->toArray(),
        'revenue_by_payment'   => $byPayment->map(fn($p) => array_merge($p, ['revenue' => $p['total_amount'], 'revenue_brut' => $p['total_amount'], 'payment_method' => $p['method'], 'count' => $p['orders_count']]))->values()->toArray(),
        'vs_previous'          => [
            'revenue'    => $prevRevenue,
            'orders'     => $calcPrev->validOrdersCount(),
            'change_pct' => $changePct,
        ],
        'daily_revenue' => $dailyRevenue,
    ];
}
```

- [ ] **Step 2 : Vérifier la syntaxe**

```bash
php -l app/Livewire/Restaurant/Reports.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3 : Commit**

```bash
git add app/Livewire/Restaurant/Reports.php
git commit -m "feat(reports): enrich getFinancialReport with cash/mobile split, cancellations, vs-previous"
```

---

## Task 4 : Vue — rapport journalier (reports.blade.php)

**Files:**
- Modify: `resources/views/livewire/restaurant/reports.blade.php`

**Interfaces:**
- Consumes: `$reportData` avec les clés de `getDailyReport()` (Task 2)
- Produces: Bloc `@if($reportType === 'daily')` complet avec tableau horaire + graphique barres

- [ ] **Step 1 : Ajouter l'option `daily` dans le `<select>` des types de rapport (autour de ligne 35)**

```blade
<option value="daily">Bilan Journalier</option>
```
Ajouter juste avant `<option value="sales">`.

- [ ] **Step 2 : Ajouter la section rapport journalier après `@endif` du rapport waiters (après la ligne ~392)**

```blade
{{-- Rapport Journalier --}}
@if($reportType === 'daily' && !empty($data))
    <div class="space-y-4 sm:space-y-6">

        {{-- KPI du jour --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">CA du jour</p>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">Commandes</p>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900">{{ $data['total_orders'] ?? 0 }}</p>
                <p class="text-xs text-neutral-400">Panier moyen : {{ number_format($data['average_ticket'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4 border-l-4 border-green-400">
                <p class="text-xs text-neutral-500 mb-1">Espèces à encaisser</p>
                <p class="text-xl sm:text-2xl font-bold text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4 border-l-4 border-blue-400">
                <p class="text-xs text-neutral-500 mb-1">Mobile Money</p>
                <p class="text-xl sm:text-2xl font-bold text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
        </div>

        {{-- Annulations + Heure de pointe --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div class="card p-3 sm:p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">Commandes annulées</p>
                    <p class="text-lg font-bold text-red-600">{{ $data['cancelled_count'] ?? 0 }} <span class="text-sm font-normal text-neutral-500">({{ number_format($data['cancelled_lost'] ?? 0, 0, ',', ' ') }} F perdus)</span></p>
                </div>
            </div>
            <div class="card p-3 sm:p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">Heure de pointe</p>
                    <p class="text-lg font-bold text-amber-700">{{ $data['peak_hour'] ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Tableau horaire --}}
        <div class="card overflow-hidden">
            <div class="p-3 sm:p-4 border-b border-neutral-100">
                <h2 class="text-base sm:text-lg font-bold text-neutral-900">Détail par heure de caisse</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-neutral-500 uppercase">Tranche</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">Commandes</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">CA Total</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">Espèces</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">Mobile</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($data['by_hour'] ?? [] as $row)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3 font-semibold text-sm text-neutral-900">{{ $row['hour_label'] }}</td>
                                <td class="px-4 py-3 text-right text-sm text-neutral-700 tabular-nums">{{ $row['orders_count'] }}</td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ number_format($row['total_amount'], 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3 text-right text-sm text-green-700 tabular-nums">{{ number_format($row['cash_amount'], 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-700 tabular-nums">{{ number_format($row['mobile_amount'], 0, ',', ' ') }} F</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-neutral-400 text-sm">Aucune vente sur cette période.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($data['by_hour']))
                    <tfoot class="bg-neutral-50 border-t-2 border-neutral-300">
                        <tr>
                            <td class="px-4 py-3 font-bold text-sm text-neutral-900 uppercase">Total</td>
                            <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ $data['total_orders'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-right font-bold text-sm text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</td>
                            <td class="px-4 py-3 text-right font-bold text-sm text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Répartition paiements --}}
        @if(!empty($data['by_payment']))
        <div class="card p-3 sm:p-4">
            <h2 class="text-base font-bold text-neutral-900 mb-3">Répartition par moyen de paiement</h2>
            <div class="space-y-2">
                @foreach($data['by_payment'] as $pay)
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $pay['is_cash'] ? 'bg-green-50' : 'bg-blue-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $pay['is_cash'] ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                            <span class="font-medium text-sm text-neutral-800">{{ $pay['label'] }}</span>
                            <span class="text-xs text-neutral-500">{{ $pay['orders_count'] }} cmd</span>
                        </div>
                        <span class="font-bold text-sm tabular-nums {{ $pay['is_cash'] ? 'text-green-700' : 'text-blue-700' }}">
                            {{ number_format($pay['total_amount'], 0, ',', ' ') }} F
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
@endif
```

- [ ] **Step 3 : Vérifier la syntaxe Blade (pas d'erreur de compilation)**

```bash
php artisan view:clear && php artisan route:list --name=restaurant 2>&1 | head -5
```
Expected: pas d'erreur de compilation.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/livewire/restaurant/reports.blade.php
git commit -m "feat(reports): add daily report view with hourly table and payment breakdown"
```

---

## Task 5 : Vue — enrichir le rapport Financier

**Files:**
- Modify: `resources/views/livewire/restaurant/reports.blade.php:300-338`

**Interfaces:**
- Consumes: `$data` avec les nouvelles clés de `getFinancialReport()` (Task 3) : `cash_total`, `mobile_total`, `cancelled_count`, `cancelled_lost`, `vs_previous`, `by_payment_detailed`

- [ ] **Step 1 : Remplacer la section `<!-- Financial Report -->` (de `@if($reportType === 'financial')` à `@endif`) par :**

```blade
{{-- Rapport Financier --}}
@if($reportType === 'financial' && !empty($data))
    <div class="space-y-4 sm:space-y-6">

        {{-- KPI principaux --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">Revenus totaux</p>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                @if(isset($data['vs_previous']['change_pct']))
                    <p class="text-xs mt-1 {{ $data['vs_previous']['change_pct'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $data['vs_previous']['change_pct'] >= 0 ? '+' : '' }}{{ $data['vs_previous']['change_pct'] }}% vs période préc.
                    </p>
                @endif
            </div>
            <div class="card p-3 sm:p-4 border-l-4 border-green-400">
                <p class="text-xs text-neutral-500 mb-1">Espèces à encaisser</p>
                <p class="text-xl sm:text-2xl font-bold text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4 border-l-4 border-blue-400">
                <p class="text-xs text-neutral-500 mb-1">Mobile Money reçu</p>
                <p class="text-xl sm:text-2xl font-bold text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4 border-l-4 border-red-400">
                <p class="text-xs text-neutral-500 mb-1">Annulées (pertes)</p>
                <p class="text-xl sm:text-2xl font-bold text-red-600">{{ $data['cancelled_count'] ?? 0 }}</p>
                <p class="text-xs text-neutral-400 tabular-nums">{{ number_format($data['cancelled_lost'] ?? 0, 0, ',', ' ') }} F perdus</p>
            </div>
        </div>

        {{-- Ligne de totaux secondaires --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">Sous-total</p>
                <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_subtotal'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">Frais livraison</p>
                <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_delivery_fees'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
            <div class="card p-3 sm:p-4">
                <p class="text-xs text-neutral-500 mb-1">Réductions</p>
                <p class="text-base font-bold text-red-600 tabular-nums">-{{ number_format($data['total_discounts'] ?? 0, 0, ',', ' ') }} F</p>
            </div>
        </div>

        {{-- Comparaison période précédente --}}
        @if(isset($data['vs_previous']))
        <div class="card p-3 sm:p-4 bg-neutral-50 border border-neutral-200">
            <h2 class="text-sm font-bold text-neutral-700 mb-3">Comparaison avec la période précédente</h2>
            <div class="flex items-center gap-6">
                <div>
                    <p class="text-xs text-neutral-500">CA période actuelle</p>
                    <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="text-neutral-300 text-xl">vs</div>
                <div>
                    <p class="text-xs text-neutral-500">CA période précédente</p>
                    <p class="text-base font-bold text-neutral-500 tabular-nums">{{ number_format($data['vs_previous']['revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="ml-auto px-3 py-1.5 rounded-xl text-sm font-bold {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $data['vs_previous']['change_pct'] ?? 0 }}%
                </div>
            </div>
        </div>
        @endif

        {{-- Détail par moyen de paiement --}}
        <div class="card p-3 sm:p-4">
            <h2 class="text-base font-bold text-neutral-900 mb-3">Détail par moyen de paiement</h2>
            <div class="space-y-2">
                @forelse($data['by_payment_detailed'] ?? [] as $pay)
                    <div class="flex items-center justify-between p-3 rounded-xl {{ $pay['is_cash'] ? 'bg-green-50' : 'bg-blue-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $pay['is_cash'] ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                            <div>
                                <p class="font-medium text-sm text-neutral-800">{{ $pay['label'] }}</p>
                                <p class="text-xs text-neutral-500">{{ $pay['orders_count'] }} transaction(s)</p>
                            </div>
                        </div>
                        <span class="font-bold text-sm tabular-nums {{ $pay['is_cash'] ? 'text-green-700' : 'text-blue-700' }}">
                            {{ number_format($pay['total_amount'], 0, ',', ' ') }} F
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-neutral-400 text-center py-4">Aucun paiement sur cette période.</p>
                @endforelse
            </div>
        </div>

    </div>
@endif
```

- [ ] **Step 2 : Vérifier la syntaxe Blade**

```bash
php artisan view:clear 2>&1 | head -5
```
Expected: pas d'erreur.

- [ ] **Step 3 : Commit**

```bash
git add resources/views/livewire/restaurant/reports.blade.php
git commit -m "feat(reports): enrich financial report view with cash/mobile split, cancellations, vs-previous"
```

---

## Task 6 : Dashboard — KPI temps réel (aujourd'hui vs hier)

**Files:**
- Modify: `app/Livewire/Restaurant/Dashboard.php`
- Modify: `resources/views/livewire/restaurant/dashboard.blade.php`

**Interfaces:**
- Consumes: `RevenueCalculator` existant + `cancellationStats()` de Task 1
- Produces: nouvelle computed `todayKpi(): array` retournant :
```php
[
  'cash_today'        => int,
  'mobile_today'      => int,
  'cancelled_today'   => int,
  'cancelled_lost'    => int,
  'peak_hour'         => string,
  'vs_yesterday_pct'  => int,
]
```

- [ ] **Step 1 : Ajouter `todayKpi()` dans `Dashboard.php` après la méthode `stats()` (ligne ~77)**

```php
#[Computed]
public function todayKpi(): array
{
    $restaurant = auth()->user()->restaurant;
    if (!$restaurant) {
        return ['cash_today' => 0, 'mobile_today' => 0, 'cancelled_today' => 0, 'cancelled_lost' => 0, 'peak_hour' => '—', 'vs_yesterday_pct' => 0];
    }

    $calc = RevenueCalculator::for($restaurant->id, today()->startOfDay(), now());
    $byPayment = $calc->revenueByPaymentMethodDetailed();
    $cancelled = $calc->cancellationStats();
    $byHour    = $calc->revenueByHourWithPayment();
    $peak      = $byHour->sortByDesc('orders_count')->first();

    $yesterday     = RevenueCalculator::for($restaurant->id, now()->subDay()->startOfDay(), now()->subDay()->endOfDay());
    $revYesterday  = $yesterday->grossRevenue();
    $revToday      = $calc->grossRevenue();
    $changePct     = $revYesterday > 0
        ? (int) round((($revToday - $revYesterday) / $revYesterday) * 100)
        : ($revToday > 0 ? 100 : 0);

    return [
        'cash_today'       => (int) $byPayment->where('is_cash', true)->sum('total_amount'),
        'mobile_today'     => (int) $byPayment->where('is_cash', false)->sum('total_amount'),
        'cancelled_today'  => $cancelled['count'],
        'cancelled_lost'   => $cancelled['total_lost'],
        'peak_hour'        => $peak ? $peak['hour_label'] : '—',
        'vs_yesterday_pct' => $changePct,
    ];
}
```

- [ ] **Step 2 : S'assurer que `RevenueCalculator` est importé dans `Dashboard.php`**

Vérifier en haut du fichier. Si absent, ajouter :
```php
use App\Services\RevenueCalculator;
```

- [ ] **Step 3 : Ajouter le widget KPI dans la vue Dashboard, juste après les 4 stat cards** (après la `</div>` de `grid grid-cols-2 lg:grid-cols-4`)

```blade
{{-- Widget KPI temps réel du jour --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4">
        <p class="text-[10px] sm:text-xs font-semibold text-green-600 uppercase tracking-wide mb-0.5">Espèces aujourd'hui</p>
        <p class="text-lg sm:text-xl font-bold text-green-800 tabular-nums leading-tight">{{ number_format($this->todayKpi['cash_today'], 0, ',', ' ') }} F</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4">
        <p class="text-[10px] sm:text-xs font-semibold text-blue-600 uppercase tracking-wide mb-0.5">Mobile Money</p>
        <p class="text-lg sm:text-xl font-bold text-blue-800 tabular-nums leading-tight">{{ number_format($this->todayKpi['mobile_today'], 0, ',', ' ') }} F</p>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4">
        <p class="text-[10px] sm:text-xs font-semibold text-red-600 uppercase tracking-wide mb-0.5">Annulées</p>
        <p class="text-lg sm:text-xl font-bold text-red-700 leading-tight">
            {{ $this->todayKpi['cancelled_today'] }}
            <span class="text-xs font-normal text-neutral-500">({{ number_format($this->todayKpi['cancelled_lost'], 0, ',', ' ') }} F)</span>
        </p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 sm:p-4">
        <p class="text-[10px] sm:text-xs font-semibold text-amber-600 uppercase tracking-wide mb-0.5">Heure de pointe</p>
        <p class="text-lg sm:text-xl font-bold text-amber-800 leading-tight">{{ $this->todayKpi['peak_hour'] }}</p>
        <p class="text-[10px] text-neutral-500">
            {{ $this->todayKpi['vs_yesterday_pct'] >= 0 ? '+' : '' }}{{ $this->todayKpi['vs_yesterday_pct'] }}% vs hier
        </p>
    </div>
</div>
```

- [ ] **Step 4 : Vérifier la syntaxe PHP + Blade**

```bash
php -l app/Livewire/Restaurant/Dashboard.php && php artisan view:clear 2>&1 | head -5
```
Expected: `No syntax errors` + pas d'erreur view:clear.

- [ ] **Step 5 : Commit**

```bash
git add app/Livewire/Restaurant/Dashboard.php resources/views/livewire/restaurant/dashboard.blade.php
git commit -m "feat(dashboard): add real-time KPI widget with cash/mobile split, cancellations, peak hour"
```

---

## Task 7 : Push final + déploiement

- [ ] **Step 1 : Vérifier que tous les commits sont propres**

```bash
git log --oneline -6
```
Expected: 5 nouveaux commits au-dessus de `975061f`.

- [ ] **Step 2 : Push**

```bash
git push origin main
```

- [ ] **Step 3 : Déployer sur le serveur**

```bash
# Sur le serveur menupro.ci :
bash ~/deploy.sh
```

- [ ] **Step 4 : Tester manuellement**

1. Aller sur `/dashboard` → vérifier le widget KPI (4 cartes supplémentaires)
2. Aller sur `/dashboard/rapports` → changer type = "Bilan Journalier" → vérifier tableau horaire
3. Changer type = "Financier" → vérifier comparaison période précédente + détail espèces/mobile
4. Changer la date de début/fin → vérifier que les données changent

---

## Self-Review

### Spec coverage
- ✅ Rapport journalier avec tranches horaires : Tasks 1, 2, 4
- ✅ Espèces vs mobile money séparés : Tasks 1, 2, 3, 4, 5
- ✅ Commandes annulées avec montant perdu : Tasks 1, 2, 3, 4, 5
- ✅ Heure de pointe : Tasks 1, 2, 4, 6
- ✅ Comparaison période précédente : Tasks 3, 5
- ✅ KPI temps réel dashboard : Task 6
- ✅ Rapport financier enrichi : Tasks 3, 5

### Type consistency
- `revenueByHourWithPayment()` retourne `Collection` avec clés `hour_label`, `cash_amount`, `mobile_amount` — utilisées dans Tasks 2 et 4 ✅
- `revenueByPaymentMethodDetailed()` retourne `Collection` avec `is_cash`, `label`, `total_amount` — utilisées dans Tasks 2, 3, 5, 6 ✅
- `cancellationStats()` retourne `['count' => int, 'total_lost' => int]` — utilisé dans Tasks 2, 3, 6 ✅
- `todayKpi` dans Dashboard est en `#[Computed]` → accessible via `$this->todayKpi` dans la vue ✅

### Placeholder scan
Aucun TBD ni TODO. Chaque étape contient le code complet. ✅
