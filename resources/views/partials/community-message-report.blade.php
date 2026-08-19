@php
    $reportRoute = $reportRoute ?? null;
    if (! $reportRoute && isset($message)) {
        if (request()->routeIs('mentor.*')) {
            $reportRoute = route('mentor.community.messages.report', $message);
        } elseif (request()->routeIs('mentee.*')) {
            $reportRoute = route('mentee.community.messages.report', $message);
        } elseif (request()->routeIs('admin.*')) {
            $reportRoute = route('admin.community.messages.report', $message);
        }
    }
    $canReport = $reportRoute
        && auth()->check()
        && (int) ($message->user_id ?? 0) !== (int) auth()->id();
@endphp
@if($canReport)
<form method="POST" action="{{ $reportRoute }}" class="{{ $formClass ?? 'inline' }}" onsubmit="return confirm('Report this post? It will be hidden from your view.')">
    @csrf
    <button type="submit" class="{{ $buttonClass ?? 'btn btn-ghost btn-sm' }}" title="Report post">🚩 Report</button>
</form>
@endif
