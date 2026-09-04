<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class DeliveryDriverController extends Controller
{
    public function index(Request $request): View
    {
        $query = DeliveryDriver::with('user')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->status, function ($q) use ($request) {
                match ($request->status) {
                    'approved'  => $q->where('verification_status', 'approved'),
                    'pending'   => $q->where('verification_status', 'pending'),
                    'rejected'  => $q->where('verification_status', 'rejected'),
                    'suspended' => $q->where('is_active', false),
                    'online'    => $q->where('is_active', true)->where('is_available', true)->where('verification_status', 'approved'),
                    default => null,
                };
            })
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->latest();

        $drivers = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => DeliveryDriver::count(),
            'approved' => DeliveryDriver::where('verification_status', 'approved')->count(),
            'pending'  => DeliveryDriver::where('verification_status', 'pending')->count(),
            'online'   => DeliveryDriver::where('is_active', true)->where('is_available', true)->where('verification_status', 'approved')->count(),
        ];

        $cities = DeliveryDriver::select('city')->distinct()->whereNotNull('city')->pluck('city');

        return view('pages.super-admin.drivers.index', compact('drivers', 'stats', 'cities'));
    }

    public function create(): View
    {
        $cities = DeliveryCity::where('is_active', true)->orderBy('name')->pluck('name');

        return view('pages.super-admin.drivers.create', compact('cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'required|string|max:20|unique:delivery_drivers,phone',
            'email'        => 'nullable|email|max:150|unique:users,email',
            'password'     => ['required', Password::min(6)],
            'city'         => 'required|string|max:100',
            'zone'         => 'nullable|string|max:100',
            'vehicle_type' => 'required|in:moto,velo,voiture',
            'vehicle_plate'=> 'nullable|string|max:20',
            'cni_number'   => 'nullable|string|max:30',
            'approve_now'  => 'nullable|boolean',
            'photo'        => 'nullable|file|image|max:5120',
            'cni_photo'    => 'nullable|file|image|max:5120',
            'license_photo'=> 'nullable|file|image|max:5120',
            'vehicle_photo'=> 'nullable|file|image|max:5120',
        ]);

        $storeFile = fn($file, $dir) => $file ? $file->store($dir, 'public') : null;
        $approveNow = (bool) ($data['approve_now'] ?? false);

        $driver = DB::transaction(function () use ($data, $request, $storeFile, $approveNow) {
            // Réutiliser un User orphelin (même téléphone, pas encore de livreur)
            $user = User::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name'     => $data['name'],
                    'email'    => $data['email'] ?? null,
                    'password' => Hash::make($data['password']),
                ]
            );
            $user->role = UserRole::DELIVERY_DRIVER;
            $user->save();

            return DeliveryDriver::create([
                'user_id'             => $user->id,
                'name'                => $data['name'],
                'phone'               => $data['phone'],
                'email'               => $data['email'] ?? null,
                'city'                => $data['city'],
                'zone'                => $data['zone'] ?? null,
                'vehicle_type'        => $data['vehicle_type'],
                'vehicle_plate'       => $data['vehicle_plate'] ?? null,
                'token'               => Str::random(64),
                'cni_number'          => $data['cni_number'] ?? null,
                'photo_path'          => $storeFile($request->file('photo'), 'drivers/photos'),
                'cni_photo_path'      => $storeFile($request->file('cni_photo'), 'drivers/cni'),
                'license_photo_path'  => $storeFile($request->file('license_photo'), 'drivers/license'),
                'vehicle_photo_path'  => $storeFile($request->file('vehicle_photo'), 'drivers/vehicle'),
                'verification_status' => $approveNow ? 'approved' : 'pending',
                'is_active'           => $approveNow,
                'is_available'        => false,
            ]);
        });

        return redirect()
            ->route('super-admin.drivers.show', $driver)
            ->with('success', "Livreur {$driver->name} créé" . ($approveNow ? ' et approuvé' : ' en attente de validation') . '.');
    }

    public function show(DeliveryDriver $driver): View
    {
        $recentDeliveries = $driver->deliveries()->latest()->limit(20)->get();
        $totalEarnings = $driver->earnings()->sum('net_amount');
        $deliveriesThisMonth = $driver->deliveries()->whereMonth('created_at', now()->month)->count();

        return view('pages.super-admin.drivers.show', compact('driver', 'recentDeliveries', 'totalEarnings', 'deliveriesThisMonth'));
    }

    public function approve(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['verification_status' => 'approved', 'is_active' => true]);
        return back()->with('success', "Livreur {$driver->name} approuvé.");
    }

    public function reject(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['verification_status' => 'rejected', 'is_active' => false]);
        return back()->with('success', "Livreur {$driver->name} rejeté.");
    }

    public function suspend(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['is_active' => false, 'is_available' => false]);
        return back()->with('success', "Livreur {$driver->name} suspendu.");
    }

    public function reactivate(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['is_active' => true]);
        return back()->with('success', "Livreur {$driver->name} réactivé.");
    }
}
