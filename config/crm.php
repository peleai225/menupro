<?php

return [
    'commissions' => [
        'commercial_first_payment_cents' => (int) env('CRM_COMMISSION_COMMERCIAL_FIRST', 500000),
        'commercial_recurring_cents' => (int) env('CRM_COMMISSION_COMMERCIAL_RECURRING', 100000),
        'commercial_recurring_enabled' => (bool) env('CRM_COMMISSION_RECURRING_ENABLED', false),
        'technician_install_cents' => (int) env('CRM_COMMISSION_TECHNICIAN_INSTALL', 300000),
        'leader_per_conversion_cents' => (int) env('CRM_COMMISSION_LEADER_CONVERSION', 100000),
        'bonus_grade_commando_cents' => (int) env('CRM_BONUS_GRADE_COMMANDO', 2500000),
        'bonus_grade_elite_cents' => (int) env('CRM_BONUS_GRADE_ELITE', 10000000),
        'bonus_monthly_top_cents' => (int) env('CRM_BONUS_MONTHLY_TOP', 5000000),
    ],

    'grades' => [
        'rookie' => ['min' => 0, 'max' => 5],
        'commando' => ['min' => 6, 'max' => 20],
        'elite' => ['min' => 21, 'max' => PHP_INT_MAX],
    ],

    'pipeline' => [
        'stale_warning_hours' => 48,
        'stale_alert_days' => 7,
        'stale_reassign_days' => 14,
    ],

    'inactivity' => [
        'warning_days' => 5,
        'alert_leader_days' => 10,
        'flag_review_days' => 30,
    ],

    'withdrawal' => [
        'min_amount_cents' => 500000,
        'max_per_week' => 1,
        'auto_approve_under_cents' => 5000000,
    ],

    'fraud' => [
        'max_qr_scans_per_hour' => 10,
        'suspicious_withdrawal_cents' => 5000000,
    ],
];
