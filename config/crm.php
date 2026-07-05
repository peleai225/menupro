<?php

return [
    'commissions' => [
        // Ambassadeur — signature (variable selon plan, géré dans SubscriptionPlan enum)
        // Ces valeurs sont des fallbacks si le plan est inconnu
        'commercial_first_payment_cents' => (int) env('CRM_COMMISSION_COMMERCIAL_FIRST', 300_000),

        // Ambassadeur — récurrente mensuelle (variable selon plan, géré dans SubscriptionPlan enum)
        // Fallback si plan inconnu
        'commercial_recurring_cents' => (int) env('CRM_COMMISSION_COMMERCIAL_RECURRING', 50_000),
        'commercial_recurring_enabled' => (bool) env('CRM_COMMISSION_RECURRING_ENABLED', true),

        // Technicien — installation (paliers volume sur mois calendaire)
        'technician_install_tier1_cents' => (int) env('CRM_COMMISSION_TECH_TIER1', 200_000),  // 1-5 installs → 2 000 F
        'technician_install_tier2_cents' => (int) env('CRM_COMMISSION_TECH_TIER2', 250_000),  // 6-10 installs → 2 500 F
        'technician_install_tier3_cents' => (int) env('CRM_COMMISSION_TECH_TIER3', 300_000),  // 11+ installs → 3 000 F
        'technician_install_tier1_max' => 5,
        'technician_install_tier2_max' => 10,

        // Team Leader — override par conversion dans son équipe
        'leader_per_conversion_cents' => (int) env('CRM_COMMISSION_LEADER_CONVERSION', 100_000),  // 1 000 F

        // Bonus grade (one-shot)
        'bonus_grade_commando_cents' => (int) env('CRM_BONUS_GRADE_COMMANDO', 2_500_000),   // 25 000 F
        'bonus_grade_elite_cents'    => (int) env('CRM_BONUS_GRADE_ELITE', 10_000_000),     // 100 000 F

        // Bonus mensuel top performer
        'bonus_monthly_top_cents' => (int) env('CRM_BONUS_MONTHLY_TOP', 5_000_000),
    ],

    'grades' => [
        'rookie'   => ['min' => 0,  'max' => 5],
        'commando' => ['min' => 6,  'max' => 20],
        'elite'    => ['min' => 21, 'max' => PHP_INT_MAX],
    ],

    'pipeline' => [
        'stale_warning_hours' => 48,
        'stale_alert_days'    => 7,
        'stale_reassign_days' => 14,
    ],

    'inactivity' => [
        'warning_days'      => 5,
        'alert_leader_days' => 10,
        'flag_review_days'  => 30,
    ],

    'withdrawal' => [
        'min_amount_cents'       => 500_000,
        'max_per_week'           => 1,
        'auto_approve_under_cents' => 5_000_000,
    ],

    'fraud' => [
        'max_qr_scans_per_hour'      => 10,
        'suspicious_withdrawal_cents' => 5_000_000,
    ],
];
