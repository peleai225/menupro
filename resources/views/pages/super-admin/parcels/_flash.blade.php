{{-- Messages de retour (colis / partenaires) --}}
@if(session('success'))
    <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background:rgba(61,158,98,0.10);color:var(--sa-success);border-color:rgba(61,158,98,0.25);">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background:rgba(220,38,38,0.10);color:var(--sa-danger);border-color:rgba(220,38,38,0.25);">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background:rgba(220,38,38,0.10);color:var(--sa-danger);border-color:rgba(220,38,38,0.25);">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
@endif
