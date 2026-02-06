<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($restaurant->name ?? 'Restaurant') . ' - Menu' }}</title>
    <meta name="description" content="{{ $restaurant->description ?? 'Découvrez notre menu et commandez en ligne' }}">
    
    @php
        $favicon = \App\Models\SystemSetting::get('favicon', '');
        $faviconUrl = null;
        $faviconType = 'image/png';
        
        if (!empty($favicon)) {
            try {
                $storage = \Illuminate\Support\Facades\Storage::disk('public');
                
                if ($storage->exists($favicon)) {
                    // Use request URL to get current scheme and host (works with any domain/IP)
                    $baseUrl = request()->getSchemeAndHttpHost();
                    $faviconUrl = $baseUrl . '/storage/' . $favicon;
                    
                    $extension = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
                    $faviconType = match($extension) {
                        'ico' => 'image/x-icon',
                        'svg' => 'image/svg+xml',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'image/png'
                    };
                    
                    $faviconUrl .= '?v=' . $storage->lastModified($favicon);
                }
            } catch (\Exception $e) {
                \Log::error('Favicon error: ' . $e->getMessage());
            }
        }
    @endphp
    
    @if($faviconUrl)
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:400,500,600,700" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
        .font-display { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'DM Sans', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased font-sans">
    {{ $slot }}
    
    @livewireScripts
    
    @stack('scripts')
</body>
</html>
