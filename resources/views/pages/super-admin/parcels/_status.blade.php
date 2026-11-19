@php
    $map = [
        'pending_payment'  => ['rgba(217,119,6,0.10)', 'var(--sa-warning)'],
        'searching_driver' => ['rgba(217,119,6,0.10)', 'var(--sa-warning)'],
        'assigned'         => ['rgba(59,111,212,0.10)', 'var(--sa-info)'],
        'picked_up'        => ['rgba(59,111,212,0.10)', 'var(--sa-info)'],
        'delivering'       => ['rgba(59,111,212,0.10)', 'var(--sa-info)'],
        'delivered'        => ['rgba(61,158,98,0.10)', 'var(--sa-success)'],
        'cancelled'        => ['rgba(220,38,38,0.10)', 'var(--sa-danger)'],
        'failed'           => ['rgba(220,38,38,0.10)', 'var(--sa-danger)'],
    ];
    [$bg, $fg] = $map[$status->value] ?? ['rgba(243,242,239,0.6)', 'var(--sa-muted-fg)'];
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap" style="background:{{ $bg }};color:{{ $fg }};">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $status->label() }}
</span>
