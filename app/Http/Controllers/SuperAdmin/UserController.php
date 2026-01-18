<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with('restaurant');

        // Filters
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => User::count(),
            'super_admins' => User::where('role', UserRole::SUPER_ADMIN)->count(),
            'restaurant_admins' => User::where('role', UserRole::RESTAURANT_ADMIN)->count(),
            'employees' => User::where('role', UserRole::EMPLOYEE)->count(),
        ];

        return view('pages.super-admin.users', compact('users', 'stats'));
    }

    /**
     * Store a newly created user (super admin).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['restaurant', 'activityLogs' => fn($q) => $q->latest()->limit(20)]);

        return view('pages.super-admin.user-show', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = $request->only('name', 'email', 'phone');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        // Prevent self-reset
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas réinitialiser votre propre mot de passe depuis ici.');
        }

        // Generate a random password
        $newPassword = \Illuminate\Support\Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        // Optionally send email with new password
        // Mail::to($user->email)->send(new PasswordResetNotification($newPassword));

        return back()->with('success', "Mot de passe réinitialisé. Nouveau mot de passe: {$newPassword}");
    }

    /**
     * Toggle user active status.
     */
    public function toggle(User $user): RedirectResponse
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous désactiver vous-même.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Utilisateur {$status}.");
    }

    /**
     * Change user role.
     */
    public function changeRole(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:super_admin,restaurant_admin,employee'],
            'restaurant_id' => ['required_if:role,restaurant_admin,employee', 'nullable', 'exists:restaurants,id'],
        ]);

        // Prevent self role change
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas changer votre propre rôle.');
        }

        $user->update([
            'role' => UserRole::from($request->role),
            'restaurant_id' => in_array($request->role, ['restaurant_admin', 'employee']) 
                ? $request->restaurant_id 
                : null,
        ]);

        return back()->with('success', 'Rôle modifié avec succès.');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('super-admin.utilisateurs.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}

