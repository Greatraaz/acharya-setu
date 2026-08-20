@extends('frontend.layouts.app')
@section('title', $channel->name.' — Community')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        @include('partials.community-whatsapp-show', [
            'showRouteName' => 'mentee.community.show',
            'storeRouteName' => 'mentee.community.messages.store',
            'likeRouteName' => 'mentee.community.messages.like',
            'allChannelsRoute' => route('mentee.community.index'),
        ])
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleReply(id) {
    const el = document.getElementById('reply-' + id);
    if (!el) return;
    const open = el.style.display === 'block';
    document.querySelectorAll('[id^="reply-"]').forEach(node => {
        if (/^reply-\d+$/.test(node.id)) node.style.display = 'none';
    });
    if (!open) {
        el.style.display = 'block';
        el.querySelector('input[name="body"]')?.focus();
    }
}
</script>
@include('partials.community-composer-scripts')
@endpush
