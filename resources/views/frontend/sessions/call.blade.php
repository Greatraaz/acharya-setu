@extends('frontend.layouts.app')
@section('title', 'Session Call — Vedrix')

@push('styles')
<style>
.call-room { padding-top: var(--nav-h); min-height: 100vh; background: #0b0f14; color: #fff; }
.call-room__stage { position: relative; height: calc(100vh - var(--nav-h)); overflow: hidden; }
.call-room__remote { width: 100%; height: 100%; background: #111827; transition: margin-right .25s ease; }
.call-room__remote.is-notes-open { margin-right: 340px; }
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
    z-index: 3; transition: right .25s ease;
}
.call-room.is-notes-open .call-room__local { right: 356px; }
.call-room__local video { width: 100%; height: 100%; object-fit: cover; }
.call-room__bar {
    position: absolute; left: 0; right: 0; bottom: 0; z-index: 4;
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 16px 20px calc(20px + env(safe-area-inset-bottom));
    background: linear-gradient(transparent, rgba(0,0,0,.72));
    transition: right .25s ease;
}
.call-room.is-notes-open .call-room__bar { right: 340px; }
.call-room__meta { min-width: 0; }
.call-room__title { font-weight: 800; font-size: 16px; }
.call-room__sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
.call-room__controls { display: flex; gap: 10px; flex-wrap: wrap; }
.call-room__btn {
    width: 48px; height: 48px; border-radius: 50%; border: 0; cursor: pointer;
    background: rgba(255,255,255,.12); color: #fff; font-size: 18px;
}
.call-room__btn.is-active { background: rgba(59,130,246,.45); }
.call-room__btn.is-off { background: #ef4444; }
.call-room__btn--end { width: auto; padding: 0 18px; border-radius: 999px; background: #dc2626; font-size: 14px; font-weight: 700; }
.call-room__status {
    position: absolute; left: 16px; top: 16px; z-index: 3;
    background: rgba(0,0,0,.55); border-radius: 999px; padding: 6px 12px; font-size: 12px;
}
.call-room__notes {
    position: absolute; top: 0; right: 0; bottom: 0; width: 340px; max-width: 100%;
    background: #111827; border-left: 1px solid rgba(255,255,255,.1);
    z-index: 5; display: flex; flex-direction: column;
    transform: translateX(100%); transition: transform .25s ease;
}
.call-room.is-notes-open .call-room__notes { transform: translateX(0); }
.call-room__notes-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 16px 16px 12px; border-bottom: 1px solid rgba(255,255,255,.08);
}
.call-room__notes-title { font-size: 15px; font-weight: 800; }
.call-room__notes-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.call-room__notes-close {
    width: 32px; height: 32px; border-radius: 8px; border: 0; cursor: pointer;
    background: rgba(255,255,255,.08); color: #fff; font-size: 16px;
}
.call-room__notes-body { flex: 1; display: flex; flex-direction: column; padding: 12px 16px 16px; min-height: 0; }
.call-room__notes-textarea {
    flex: 1; width: 100%; min-height: 200px; resize: none; border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px; background: #0b1220; color: #e2e8f0; padding: 12px 14px;
    font-size: 14px; line-height: 1.6; font-family: inherit;
}
.call-room__notes-textarea:focus { outline: none; border-color: rgba(59,130,246,.6); }
.call-room__notes-foot {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-top: 10px; font-size: 12px; color: #94a3b8;
}
.call-room__notes-status.is-saving { color: #fbbf24; }
.call-room__notes-status.is-saved { color: #34d399; }
.call-room__notes-status.is-error { color: #f87171; }
@media (max-width: 768px) {
    .call-room__remote.is-notes-open { margin-right: 0; }
    .call-room.is-notes-open .call-room__local { right: 16px; top: auto; bottom: 96px; }
    .call-room.is-notes-open .call-room__bar { right: 0; }
    .call-room__notes { width: 100%; }
}
@media (max-width: 640px) {
    .call-room__local { width: 120px; height: 90px; right: 10px; top: 10px; }
    .app-bottom-nav { display: none !important; }
}
</style>
@endpush

@section('content')
<div class="call-room"
     data-token-url="{{ $tokenUrl }}"
     data-end-url="{{ $endUrl }}"
     data-notes-url="{{ $notesUrl }}"
     data-back-url="{{ $backUrl }}">
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

        <aside class="call-room__notes" id="notes-panel" aria-hidden="true">
            <div class="call-room__notes-head">
                <div>
                    <div class="call-room__notes-title">📝 My Notes</div>
                    <div class="call-room__notes-hint">Private — only you can see these</div>
                </div>
                <button type="button" class="call-room__notes-close" id="btn-notes-close" title="Close notes">✕</button>
            </div>
            <div class="call-room__notes-body">
                <textarea id="session-notes" class="call-room__notes-textarea"
                          placeholder="Jot down key points, questions, action items…"></textarea>
                <div class="call-room__notes-foot">
                    <span id="notes-save-status" class="call-room__notes-status">Saved automatically</span>
                    <span>Visible after the call ends</span>
                </div>
            </div>
        </aside>

        <div class="call-room__bar">
            <div class="call-room__meta">
                <div class="call-room__title">{{ $session->title ?: 'Mentoring Session' }}</div>
                <div class="call-room__sub">with {{ $peer->name ?? 'Participant' }} · {{ $session->duration_minutes ?? 30 }} min</div>
            </div>
            <div class="call-room__controls">
                <button type="button" class="call-room__btn" id="btn-notes" title="Session notes">📝</button>
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
    const notesUrl = root.dataset.notesUrl;
    const backUrl = root.dataset.backUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let client, localAudio, localVideo, joined = false, timer, elapsed = 0, micOn = true, camOn = true;
    let notesOpen = false, notesLoaded = false, notesDirty = false, notesSaving = false, saveTimer;

    const notesTextarea = document.getElementById('session-notes');
    const notesStatus = document.getElementById('notes-save-status');
    const remotePlayer = document.getElementById('remote-player');

    function setStatus(text) {
        const el = document.getElementById('call-status-text');
        if (el) el.textContent = text;
    }

    function setNotesStatus(text, state) {
        if (!notesStatus) return;
        notesStatus.textContent = text;
        notesStatus.className = 'call-room__notes-status' + (state ? ' is-' + state : '');
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

    async function loadNotes() {
        if (notesLoaded) return;
        try {
            const res = await fetch(notesUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (res.ok && notesTextarea) {
                notesTextarea.value = data.content || '';
                notesLoaded = true;
                notesDirty = false;
                setNotesStatus('Saved automatically', 'saved');
            }
        } catch (e) {}
    }

    async function saveNotes(force) {
        if (!notesTextarea || (!notesDirty && !force) || notesSaving) return;
        notesSaving = true;
        setNotesStatus('Saving…', 'saving');
        try {
            const res = await fetch(notesUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ content: notesTextarea.value }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Could not save notes.');
            notesDirty = false;
            setNotesStatus('Saved', 'saved');
        } catch (e) {
            setNotesStatus(e.message || 'Save failed', 'error');
        } finally {
            notesSaving = false;
        }
    }

    function scheduleSave() {
        clearTimeout(saveTimer);
        setNotesStatus('Unsaved changes…', 'saving');
        saveTimer = setTimeout(() => saveNotes(false), 1500);
    }

    function toggleNotes(open) {
        notesOpen = open ?? !notesOpen;
        root.classList.toggle('is-notes-open', notesOpen);
        remotePlayer?.classList.toggle('is-notes-open', notesOpen);
        document.getElementById('notes-panel')?.setAttribute('aria-hidden', notesOpen ? 'false' : 'true');
        document.getElementById('btn-notes')?.classList.toggle('is-active', notesOpen);
        if (notesOpen) {
            loadNotes();
            notesTextarea?.focus();
        }
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
        loadNotes();
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
        clearTimeout(saveTimer);
        await saveNotes(true);
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

    notesTextarea?.addEventListener('input', () => {
        notesDirty = true;
        scheduleSave();
    });
    notesTextarea?.addEventListener('blur', () => saveNotes(false));

    document.getElementById('btn-notes')?.addEventListener('click', () => toggleNotes());
    document.getElementById('btn-notes-close')?.addEventListener('click', () => toggleNotes(false));

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
