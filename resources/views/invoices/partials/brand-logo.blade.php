@php
    $logoRelative = is_file(public_path('frontend/images/logo-light.png'))
        ? 'frontend/images/logo-light.png'
        : 'frontend/images/logo.png';
    $logoFile = public_path($logoRelative);
    $forPdf = ! empty($forPdf);
    $hasLogo = is_file($logoFile);
    $company = $invoice->seller_name ?: 'Vedrix';
    $logoSrc = ($forPdf && $hasLogo)
        ? ('file:///' . str_replace('\\', '/', $logoFile))
        : asset($logoRelative);
@endphp
@if($hasLogo)
    <img class="logo" src="{{ $logoSrc }}" alt="Vedrix">
@else
    <div class="brand-fallback">{{ $company }}</div>
@endif
