<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with('creator')
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::active()->count(),
            'scheduled' => Announcement::where('is_active', true)
                ->where('starts_at', '>', now())
                ->count(),
        ];

        return view('pages.super-admin.announcements', compact('announcements', 'stats'));
    }

    public function create(): View
    {
        return view('pages.super-admin.announcement-create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all,active,trial,expired',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_dismissible' => 'boolean',
            'show_on_dashboard' => 'boolean',
            'send_email' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_dismissible'] = $request->boolean('is_dismissible', true);
        $validated['show_on_dashboard'] = $request->boolean('show_on_dashboard', true);
        $validated['send_email'] = $request->boolean('send_email', false);

        $announcement = Announcement::create($validated);

        // Send email notification if requested
        if ($announcement->send_email) {
            $this->sendEmailNotifications($announcement);
        }

        return redirect()->route('super-admin.announcements.index')
            ->with('success', 'Annonce créée avec succès.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('pages.super-admin.announcement-edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all,active,trial,expired',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_dismissible' => 'boolean',
            'show_on_dashboard' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_dismissible'] = $request->boolean('is_dismissible', true);
        $validated['show_on_dashboard'] = $request->boolean('show_on_dashboard', true);

        $announcement->update($validated);

        return redirect()->route('super-admin.announcements.index')
            ->with('success', 'Annonce mise à jour avec succès.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('super-admin.announcements.index')
            ->with('success', 'Annonce supprimée avec succès.');
    }

    public function sendEmails(Announcement $announcement): RedirectResponse
    {
        if ($announcement->email_sent_at) {
            return back()->with('error', 'Les emails ont déjà été envoyés pour cette annonce.');
        }

        $result = $this->sendEmailNotifications($announcement);

        if ($result['failed'] > 0) {
            return back()->with('warning', "Emails envoyés : {$result['sent']} réussis, {$result['failed']} échecs.");
        }

        return back()->with('success', "Emails envoyés avec succès à {$result['sent']} destinataire(s).");
    }

    protected function sendEmailNotifications(Announcement $announcement): array
    {
        $query = User::whereHas('restaurant');

        switch ($announcement->target) {
            case 'active':
                $query->whereHas('restaurant', fn($q) => $q->where('status', 'active'));
                break;
            case 'trial':
                $query->whereHas('restaurant.subscriptions', fn($q) => $q->where('is_trial', true)->where('status', 'active'));
                break;
            case 'expired':
                $query->whereHas('restaurant', fn($q) => $q->where('status', 'expired'));
                break;
        }

        $users = $query->get();
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $user->notify(new AnnouncementNotification($announcement));
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = $user->email . ': ' . $e->getMessage();
                \Log::warning("Failed to send announcement to {$user->email}: " . $e->getMessage());
            }
        }

        $announcement->update(['email_sent_at' => now()]);

        return [
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
