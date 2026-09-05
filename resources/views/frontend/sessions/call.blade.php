@extends('frontend.layouts.app')
@section('title', 'Session Call — Vedrix')

@push('styles')
<style>
.call-room {
    padding-top: var(--nav-h);
    min-height: 100vh;
    width: 100%;
    max-width: 100vw;
    background: #0b0f14;
    color: #fff;
    box-sizing: border-box;
}
.call-room__stage {
    display: flex;
    width: 100%;
    height: calc(100vh - var(--nav-h));
    max-height: calc(100dvh - var(--nav-h));
    overflow: hidden;
}
.call-room__main {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
    height: 100%;
    background: #111827;
}
.call-room__remote { width: 100%; height: 100%; background: #111827; }
.call-room__remote video { width: 100%; height: 100%; object-fit: cover; }
.call-room__empty {
    position: absolute; inset: 0; z-index: 2;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 10px;
    text-align: center; padding: 24px;
    pointer-events: none;
}
.call-room__avatar {
    width: 96px; height: 96px; border-radius: 50%; background: #1f2937;
    display: flex; align-items: center; justify-content: center;
    font-size: 36px; font-weight: 800; overflow: hidden;
}
.call-room__avatar img { width: 100%; height: 100%; object-fit: cover; }
.call-room__local {
    position: absolute; right: 16px; top: 16px; width: 180px; height: 128px;
    border-radius: 14px; overflow: hidden; background: #1f2937;
    border: 1px solid rgba(255,255,255,.12); z-index: 3;
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
.call-room__controls { display: flex; gap: 10px; flex-wrap: wrap; margin-left: auto; }
.call-room__btn {
    width: 48px; height: 48px; border-radius: 50%; border: 0; cursor: pointer;
    background: rgba(255,255,255,.12); color: #fff; font-size: 18px;
}
.call-room__btn.is-active { background: rgba(59,130,246,.45); }
.call-room__btn.is-off { background: #ef4444; }
.call-room__btn--end {
    width: auto; padding: 0 18px; border-radius: 999px;
    background: #dc2626; font-size: 14px; font-weight: 700;
}
.call-room__status {
    position: absolute; left: 16px; top: 16px; z-index: 3;
    background: rgba(0,0,0,.55); border-radius: 999px; padding: 6px 12px; font-size: 12px;
}
.call-room__notes {
    flex: 0 0 0;
    width: 0;
    max-width: 100%;
    height: 100%;
    background: #111827;
    border-left: 0 solid rgba(255,255,255,.1);
    display: flex; flex-direction: column;
    overflow: hidden;
    opacity: 0;
    transition: flex-basis .25s ease, width .25s ease, opacity .2s ease, border-left-width .25s ease;
}
.call-room.is-notes-open .call-room__notes {
    flex-basis: min(340px, 100%);
    width: min(340px, 100%);
    border-left-width: 1px;
    opacity: 1;
}
.call-room__notes-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 16px 16px 12px; border-bottom: 1px solid rgba(255,255,255,.08);
    flex-shrink: 0;
}
.call-room__notes-title { font-size: 15px; font-weight: 800; white-space: nowrap; }
.call-room__notes-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.call-room__notes-close {
    width: 32px; height: 32px; border-radius: 8px; border: 0; cursor: pointer;
    background: rgba(255,255,255,.08); color: #fff; font-size: 16px; flex-shrink: 0;
}
.call-room__notes-body {
    flex: 1; display: flex; flex-direction: column;
    padding: 12px 16px 16px; min-height: 0; min-width: 260px;
}
.call-room__notes-textarea {
    flex: 1; width: 100%; min-height: 200px; resize: none;
    border: 1px solid rgba(255,255,255,.12); border-radius: 12px;
    background: #0b1220; color: #e2e8f0; padding: 12px 14px;
    font-size: 14px; line-height: 1.6; font-family: inherit;
}
.call-room__notes-textarea:focus { outline: none; border-color: rgba(59,130,246,.6); }
.call-room__notes-foot {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    margin-top: 10px; font-size: 12px; color: #94a3b8; flex-shrink: 0;
}
.call-room__notes-status.is-saving { color: #fbbf24; }
.call-room__notes-status.is-saved { color: #34d399; }
.call-room__notes-status.is-error { color: #f87171; }
.call-extend {
    position: absolute; inset: 0; z-index: 20;
    display: none; align-items: center; justify-content: center;
    background: rgba(0,0,0,.55); padding: 20px;
}
.call-extend.is-open { display: flex; }
.call-extend__card {
    width: 100%; max-width: 400px; background: #111827; border-radius: 16px;
    border: 1px solid rgba(255,255,255,.12); padding: 22px 20px 18px; text-align: center;
}
.call-extend__title { font-size: 18px; font-weight: 800; margin-bottom: 8px; }
.call-extend__body { font-size: 14px; color: #cbd5e1; line-height: 1.5; }
.call-extend__actions { display: flex; gap: 10px; margin-top: 18px; }
.call-extend__btn {
    flex: 1; height: 44px; border: 0; border-radius: 10px; cursor: pointer;
    font-weight: 700; font-size: 14px;
}
.call-extend__btn--yes { background: #16a34a; color: #fff; }
.call-extend__btn--no { background: rgba(255,255,255,.12); color: #fff; }
.call-extend-banner {
    display: none; position: absolute; left: 50%; top: 16px; transform: translateX(-50%);
    z-index: 6; max-width: calc(100% - 32px);
    background: rgba(15,23,42,.88); border: 1px solid rgba(255,255,255,.12);
    border-radius: 12px; padding: 8px 14px; font-size: 12px; color: #e2e8f0; text-align: center;
}
.call-extend-banner.is-visible { display: block; }

/* Full-bleed call page: hide site chrome that steals space */
body:has(.call-room) .footer,
body:has(.call-room) .app-bottom-nav { display: none !important; }
body:has(.call-room) { overflow: hidden; }

@media (max-width: 768px) {
    .call-room__stage { flex-direction: column; }
    .call-room.is-notes-open .call-room__notes {
        position: absolute; inset: 0; z-index: 8;
        flex-basis: auto; width: 100%; opacity: 1;
    }
    .call-room.is-notes-open .call-room__local { right: 16px; top: auto; bottom: 96px; }
}
@media (max-width: 640px) {
    .call-room__local { width: 120px; height: 90px; right: 10px; top: 10px; }
    .call-room__controls { width: 100%; justify-content: center; margin-left: 0; }
    .call-room__meta { width: 100%; text-align: center; }
    .call-room__bar { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="call-room"
     data-token-url="{{ $tokenUrl }}"
     data-end-url="{{ $endUrl }}"
     data-notes-url="{{ $notesUrl }}"
     data-back-url="{{ $backUrl }}"
     data-is-mentee="{{ !empty($isMentee) ? '1' : '0' }}"
     data-scheduled-end="{{ $scheduledEndTs ?? '' }}"
     data-server-now="{{ $serverNowTs ?? '' }}"
     data-duration="{{ (int) ($session->duration_minutes ?? 30) }}">
    <div class="call-room__stage">
        <div class="call-room__main">
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
            <div class="call-extend-banner" id="extend-banner"></div>

            <div class="call-extend" id="extend-modal" role="dialog" aria-modal="true" aria-labelledby="extend-title">
                <div class="call-extend__card">
                    <div class="call-extend__title" id="extend-title">Continue this session?</div>
                    <div class="call-extend__body" id="extend-body">
                        This session is about to end. Would you like 10 more minutes? There is no extra charge.
                    </div>
                    <div class="call-extend__actions">
                        <button type="button" class="call-extend__btn call-extend__btn--yes" id="extend-yes">Yes, continue</button>
                        <button type="button" class="call-extend__btn call-extend__btn--no" id="extend-no">No, end on time</button>
                    </div>
                </div>
            </div>

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
    const isMentee = root.dataset.isMentee === '1';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const EXTEND_MS = 10 * 60 * 1000;
    const PROMPT_BEFORE_MS = 5 * 60 * 1000;
    const serverNowTs = parseInt(root.dataset.serverNow || '0', 10);
    const scheduledEndTs = parseInt(root.dataset.scheduledEnd || '0', 10);
    const clockOffsetMs = serverNowTs ? (serverNowTs * 1000) - Date.now() : 0;
    const originalEndMs = scheduledEndTs ? scheduledEndTs * 1000 : 0;

    let client, localAudio, localVideo, joined = false, timer, elapsed = 0, micOn = true, camOn = true;
    let notesOpen = false, notesLoaded = false, notesDirty = false, notesSaving = false, saveTimer;
    let leaving = false, promptShown = false, extendDecision = null, endAtMs = originalEndMs, signalTimer;
    const durationLabel = document.querySelector('.call-room__sub');
    const extendModal = document.getElementById('extend-modal');
    const extendBanner = document.getElementById('extend-banner');
    const extendBody = document.getElementById('extend-body');

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

    function nowMs() {
        return Date.now() + clockOffsetMs;
    }

    function formatRemain(ms) {
        const total = Math.max(0, Math.ceil(ms / 1000));
        const m = Math.floor(total / 60);
        const s = total % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    function setBanner(text, visible) {
        if (!extendBanner) return;
        extendBanner.textContent = text || '';
        extendBanner.classList.toggle('is-visible', !!visible && !!text);
    }

    function showExtendPrompt() {
        if (promptShown || !originalEndMs) return;
        promptShown = true;
        if (isMentee) {
            const remainMin = Math.max(1, Math.round((originalEndMs - nowMs()) / 60000));
            if (extendBody) {
                extendBody.textContent = 'This session ends in about ' + remainMin +
                    ' minute' + (remainMin === 1 ? '' : 's') +
                    '. Would you like 10 more minutes? There is no extra charge.';
            }
            extendModal?.classList.add('is-open');
        } else {
            endAtMs = originalEndMs + 20000;
            setBanner('Waiting for the mentee to choose whether to continue for 10 more minutes.', true);
        }
    }

    function hideExtendPrompt() {
        extendModal?.classList.remove('is-open');
    }

    function sendCallSignal(payload) {
        if (!client || !joined || typeof client.sendStreamMessage !== 'function') return;
        try {
            const encoded = new TextEncoder().encode(JSON.stringify(payload));
            const result = client.sendStreamMessage(encoded);
            if (result && typeof result.catch === 'function') result.catch(() => {});
        } catch (e) {}
    }

    function applyExtend(fromPeer) {
        if (extendDecision === 'yes') return;
        extendDecision = 'yes';
        endAtMs = originalEndMs + EXTEND_MS;
        hideExtendPrompt();
        setBanner('Session continued for 10 more minutes. No extra charge.', true);
        if (durationLabel && durationLabel.textContent) {
            durationLabel.textContent = durationLabel.textContent.replace(/·\s*\d+\s*min/, '· extra 10 min');
        }
        if (window.showToast) showToast('success', 'Session continued for 10 more minutes.');
        if (!fromPeer && isMentee) {
            sendCallSignal({ type: 'extend-yes' });
            clearInterval(signalTimer);
            signalTimer = setInterval(() => sendCallSignal({ type: 'extend-yes' }), 4000);
        }
    }

    function declineExtend(fromPeer) {
        if (extendDecision === 'yes') return;
        extendDecision = 'no';
        endAtMs = originalEndMs;
        hideExtendPrompt();
        setBanner('This session will end at the scheduled time.', true);
        if (!fromPeer && isMentee) sendCallSignal({ type: 'extend-no' });
    }

    function handleSignal(raw) {
        let text = '';
        try {
            if (typeof raw === 'string') text = raw;
            else if (raw instanceof ArrayBuffer) text = new TextDecoder().decode(raw);
            else if (raw && raw.buffer) text = new TextDecoder().decode(raw);
            else text = String(raw);
            const payload = JSON.parse(text);
            if (payload.type === 'extend-yes') applyExtend(true);
            if (payload.type === 'extend-no') declineExtend(true);
        } catch (e) {}
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(() => {
            elapsed += 1;
            const m = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const s = String(elapsed % 60).padStart(2, '0');
            const remain = endAtMs ? ' · ' + formatRemain(endAtMs - nowMs()) + ' left' : '';
            document.getElementById('call-timer').textContent = m + ':' + s + remain;

            if (!originalEndMs) return;
            const current = nowMs();
            if (!promptShown && current >= (originalEndMs - PROMPT_BEFORE_MS) && current < originalEndMs) {
                showExtendPrompt();
            }
            if (extendDecision === null && promptShown && current >= originalEndMs) {
                declineExtend(false);
            }
            if (endAtMs && current >= endAtMs) {
                leave('time_up');
            }
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
            if (isMentee && extendDecision) {
                sendCallSignal({ type: extendDecision === 'yes' ? 'extend-yes' : 'extend-no' });
            }
        });
        client.on('user-unpublished', (user, mediaType) => {
            if (mediaType === 'video') document.getElementById('waiting').style.display = '';
        });
        client.on('user-left', () => {
            document.getElementById('waiting').style.display = '';
            setStatus('The other person left the call.');
        });
        client.on('stream-message', (_uid, data) => handleSignal(data));

        await client.join(data.app_id, data.channel, data.token, data.uid);
        [localAudio, localVideo] = await AgoraRTC.createMicrophoneAndCameraTracks();
        localVideo.play('local-player');
        await client.publish([localAudio, localVideo]);
        joined = true;
        startTimer();
        setStatus('Waiting for ' + (data.peer?.name || 'the other person') + ' to join…');
    }

    async function leave(reason) {
        if (leaving) return;
        leaving = true;
        hideExtendPrompt();
        clearInterval(timer);
        clearInterval(signalTimer);
        clearTimeout(saveTimer);
        await saveNotes(true);
        try {
            localAudio?.close();
            localVideo?.close();
            if (client && joined) await client.leave();
        } catch (e) {}
        try {
            const res = await fetch(endUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ reason: reason || 'normal' }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.can_rejoin) {
                if (window.showToast) {
                    showToast('info', data.message || 'You can rejoin from the session page.');
                }
                await new Promise((resolve) => setTimeout(resolve, 900));
            }
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
    document.getElementById('extend-yes')?.addEventListener('click', () => applyExtend(false));
    document.getElementById('extend-no')?.addEventListener('click', () => declineExtend(false));
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
