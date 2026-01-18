<x-mail::layout>
{{-- Header --}}
<x-slot:header>
@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'MenuPro'));
@endphp
<x-mail::header :url="config('app.url')">
{{ $appName }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
@php
    $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'MenuPro'));
@endphp
<x-mail::footer>
© {{ date('Y') }} {{ $appName }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
