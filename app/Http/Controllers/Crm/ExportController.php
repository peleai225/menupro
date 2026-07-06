<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Commission;
use App\Models\Crm\DailyReport;
use App\Models\Crm\Lead;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function leads(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="leads-crm-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Restaurant', 'Contact', 'Téléphone', 'Email', 'Ville', 'Statut', 'Plan', 'Commercial', 'Équipe', 'Source', 'Date création', 'Date conversion']);

            Lead::with(['assignedUser', 'team'])
                ->when($request->status, fn($q, $s) => $q->where('status', $s))
                ->when($request->team_id, fn($q, $id) => $q->where('team_id', $id))
                ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                ->orderByDesc('created_at')
                ->chunk(500, function ($leads) use ($handle) {
                    foreach ($leads as $lead) {
                        fputcsv($handle, [
                            $lead->id,
                            $lead->restaurant_name,
                            $lead->manager_name ?? '',
                            $lead->phone ?? '',
                            $lead->email ?? '',
                            $lead->city ?? '',
                            $lead->status->value ?? $lead->status,
                            $lead->subscription_plan?->value ?? '',
                            $lead->assignedUser?->name ?? '',
                            $lead->team?->name ?? '',
                            $lead->source?->value ?? '',
                            $lead->created_at->format('d/m/Y'),
                            $lead->converted_at?->format('d/m/Y') ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    public function commissions(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="commissions-crm-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Agent', 'Type', 'Montant (FCFA)', 'Description', 'Statut', 'Date']);

            Commission::with(['user'])
                ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
                ->when($request->type, fn($q, $t) => $q->where('type', $t))
                ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                ->orderByDesc('created_at')
                ->chunk(500, function ($commissions) use ($handle) {
                    foreach ($commissions as $commission) {
                        fputcsv($handle, [
                            $commission->id,
                            $commission->user?->name ?? '',
                            $commission->type->value ?? $commission->type,
                            number_format(($commission->amount_cents ?? 0) / 100, 2),
                            $commission->description ?? '',
                            $commission->status->value ?? $commission->status,
                            $commission->created_at->format('d/m/Y H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }

    public function reports(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapports-journaliers-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Agent', 'Date', 'Visites', 'Démos', 'Nouveaux leads', 'Conversions', 'Zone couverte', 'Obstacles', 'Notes', 'Statut review']);

            DailyReport::with(['user'])
                ->when($request->user_id, fn($q, $id) => $q->where('user_id', $id))
                ->when($request->from, fn($q, $d) => $q->whereDate('report_date', '>=', $d))
                ->when($request->to, fn($q, $d) => $q->whereDate('report_date', '<=', $d))
                ->orderByDesc('report_date')
                ->chunk(500, function ($reports) use ($handle) {
                    foreach ($reports as $report) {
                        fputcsv($handle, [
                            $report->id,
                            $report->user?->name ?? '',
                            $report->report_date?->format('Y-m-d') ?? $report->created_at->format('Y-m-d'),
                            $report->visits_count ?? 0,
                            $report->demos_count ?? 0,
                            $report->new_leads_count ?? 0,
                            $report->conversions_count ?? 0,
                            $report->zone_covered ?? '',
                            $report->obstacles ?? '',
                            $report->notes ?? '',
                            $report->reviewed_at ? 'Reviewé' : 'En attente',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
