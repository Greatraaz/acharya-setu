@extends('frontend.layouts.app')
@section('title', 'Session Call — Vedrix')

@push('styles')
<style>
.call-room { padding-top: var(--nav-h); min-height: 100vh; background: #0b0f14; color: #fff; }
.call-room__stage { position: relative; height: calc(100vh - var(--nav-h)); overflow: hidden; }
.call-room__remote { width: 100%; height: 100%; background: #111827; }
.call-room__remote video { width: 100%; height: 100%; object-fit: cover; }
.call-room__empty {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 10px; text-align: center; padding: 24px;
}
.call-room__avatar {
    width: 96px; height: 96px; border-radius: 50%; background: #1f2937;
    display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800;
    overflow: hidden;
}
.call-room__avatar img { width: 100%; height: 100%; object-fit: cover; }
.call-room__local {
    position: absolute; right: 16px; top: 16px; width: 180px; height: 128px;
    border-radius: 14px; overflow: hidden; background: #1f2937; border: 1px solid rgba(255,255,255,.12);
    z-index: 3;
}
.call-room__local video { width: 100%; height: 100%; object-fit: cover; }
.call-room__bar {
    position: absolute; left: 0; right: 0; bottom: 0; z-index: 4;
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 16px 20px calc(20px + env(safe-area-inset-bottom));
    background: linear-gradient(transparent, rgba(0,0,0,.72));
}
.call-room__meta { min-width: 0; }
.call-room__title { font-weight: 800; font-size: 16px; }
.call-room__sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
.call-room__controls { display: flex; gap: 10px; flex-wrap: wrap; }
.call-room__btn {
    width: 48px; height: 48px; border-radius: 50%; border: 0; cursor: pointer;
    background: rgba(255,255,255,.12); color: #fff; font-size: 18px;
}
.call-room__btn.is-off { background: #ef4444; }
.call-room__btn--end { width: auto; padding: 0 18px; border-radius: 999px; background: #dc2626; font-size: 14px; font-weight: 700; }
.call-room__status {
    position: absolute; left: 16px; top: 16px; z-index: 3;
    background: rgba(0,0,0,.55); border-radius: 999px; padding: 6px 12px; font-size: 12px;
}
@media (max-width: 640px) {
    .call-room__local { width: 120px; height: 90px; right: 10px; top: 10px; }
    .app-bottom-nav { display: none !important; }
}
</style>
@endpush

@section('content')
<div class="call-room" data-token-url="{{ $tokenUrl }}" data-end-url="{{ $endUrl }}" data-back-url="{{ $backUrl }}">
    <div class="call-room__stage">
        <div id="remote-player" class="call-room__remote"></div>
        <div id="waiting" class="call-room__empty">
            <div class="call-room__avatar">
                @if($peer?->avatar_url)
                    <img src="{{ $peer->avatar_url }}" alt="">
                @else
                    {{ strtoupper(substr($peer->name ?? '?', 0, 1)) }}
                @endif
            </div>
            <div style="font-weight:800;font-size:18px;">{{ $peer->name ?? 'Participant' }}</div>
            <div id="call-status-text" style="font-size:13px;color:#94a3b8;">Connecting…</div>
        </div>
        <div id="local-player" class="call-room__local"></div>
        <div class="call-room__status" id="call-timer">00:00</div>
        <div class="call-room__bar">
            <div class="call-room__meta">
                <div class="call-room__title">{{ $session->title ?: 'Mentoring Session' }}</div>
                <div class="call-room__sub">with {{ $peer->name ?? 'Participant' }} · {{ $session->duration_minutes ?? 30 }} min</div>
            </div>
            <div class="call-room__controls">
                <button type="button" class="call-room__btn" id="btn-mic" title="Mute">🎤</button>
                <button type="button" class="call-room__btn" id="btn-cam" title="Camera">📷</button>
                <button type="button" class="call-room__btn call-room__btn--end" id="btn-end">End call</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.23.2.js"></script>
<script>
(function () {
    const root = document.querySelector('.call-room');
    if (!root || typeof AgoraRTC === 'undefined') {
        document.getElementById('call-status-text').textContent = 'Could not load the video SDK. Refresh and try again.';
        return;
    }

    const tokenUrl = root.dataset.tokenUrl;
    const endUrl = root.dataset.endUrl;
    const backUrl = root.dataset.backUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let client, localAudio, localVideo, joined = false, timer, elapsed = 0, micOn = true, camOn = true;

    function setStatus(text) {
        const el = document.getElementById('call-status-text');
        if (el) el.textContent = text;
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(() => {
            elapsed += 1;
            const m = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const s = String(elapsed % 60).padStart(2, '0');
            document.getElementById('call-timer').textContent = m + ':' + s;
        }, 1000);
    }

    async function fetchToken() {
        const res = await fetch(tokenUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Could not start the call.');
        }
        return data;
    }

    async function join() {
        setStatus('Connecting…');
        const data = await fetchToken();
        client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

        client.on('user-published', async (user, mediaType) => {
            await client.subscribe(user, mediaType);
            if (mediaType === 'video') {
                document.getElementById('waiting').style.display = 'none';
                user.videoTrack.play('remote-player');
            }
            if (mediaType === 'audio') user.audioTrack.play();
        });
        client.on('user-unpublished', (user, mediaType) => {
            if (mediaType === 'video') document.getElementById('waiting').style.display = '';
        });
        client.on('user-left', () => {
            document.getElementById('waiting').style.display = '';
            setStatus('The other person left the call.');
        });

        await client.join(data.app_id, data.channel, data.token, data.uid);
        [localAudio, localVideo] = await AgoraRTC.createMicrophoneAndCameraTracks();
        localVideo.play('local-player');
        await client.publish([localAudio, localVideo]);
        joined = true;
        startTimer();
        setStatus('Waiting for ' + (data.peer?.name || 'the other person') + ' to join…');
    }

    async function leave(reason) {
        clearInterval(timer);
        try {
            localAudio?.close();
            localVideo?.close();
            if (client && joined) await client.leave();
        } catch (e) {}
        try {
            await fetch(endUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ reason: reason || 'normal' }),
            });
        } catch (e) {}
        window.location.href = backUrl;
    }

    document.getElementById('btn-mic').addEventListener('click', async () => {
        if (!localAudio) return;
        micOn = !micOn;
        await localAudio.setEnabled(micOn);
        document.getElementById('btn-mic').classList.toggle('is-off', !micOn);
        document.getElementById('btn-mic').textContent = micOn ? '🎤' : '🔇';
    });
    document.getElementById('btn-cam').addEventListener('click', async () => {
        if (!localVideo) return;
        camOn = !camOn;
        await localVideo.setEnabled(camOn);
        document.getElementById('btn-cam').classList.toggle('is-off', !camOn);
        document.getElementById('btn-cam').textContent = camOn ? '📷' : '🚫';
    });
    document.getElementById('btn-end').addEventListener('click', () => leave('normal'));
    window.addEventListener('beforeunload', () => {
        if (joined) {
            const fd = new FormData();
            fd.append('_token', csrf);
            fd.append('reason', 'host_left');
            navigator.sendBeacon(endUrl, fd);
        }
    });

    join().catch((err) => {
        setStatus(err.message || 'Could not start the call.');
        if (window.showToast) showToast('error', err.message || 'Could not start the call.');
    });
})();
</script>
@endpush
