@props(['url'])
@php
    $logo = \App\Models\SystemSetting::get('logo', '');
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'MenuPro'));
    $logoUrl = null;
    
    if (!empty($logo)) {
        try {
            $storage = \Illuminate\Support\Facades\Storage::disk('public');
            if ($storage->exists($logo)) {
                // Generate absolute URL for email (required for email clients)
                $baseUrl = rtrim(config('app.url'), '/');
                $logoUrl = $baseUrl . '/storage/' . $logo;
            }
        } catch (\Exception $e) {
            \Log::warning('Logo not found for email: ' . $e->getMessage(), [
                'logo_path' => $logo,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Use app name from slot if provided, otherwise use SystemSetting
    $displayName = trim($slot) !== '' && trim($slot) !== 'Laravel' ? trim($slot) : $appName;
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
    <img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }} Logo" style="height: 75px; width: auto; max-width: 200px; display: block; margin: 15px auto 10px;">
@else
    <span style="font-size: 24px; font-weight: bold; color: #6366f1; display: block; margin: 15px 0 10px;">{{ $displayName }}</span>
@endif
</a>
</td>
</tr>
