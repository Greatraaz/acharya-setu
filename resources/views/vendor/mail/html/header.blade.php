@props(['url'])
@php
    // Absolute URL required for email clients
    $logoUrl = rtrim((string) config('app.url'), '/').'/images/logo.png';
    $appName = config('app.name', 'Vedrix');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img
    src="{{ $logoUrl }}"
    alt="{{ $appName }}"
    width="180"
    height="56"
    style="height:56px;width:auto;max-width:180px;border:0;display:block;margin:0 auto;"
>
</a>
</td>
</tr>
