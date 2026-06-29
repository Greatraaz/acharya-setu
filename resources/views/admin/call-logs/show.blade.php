@extends('admin.layouts.app')
@section('title','Call Log #' . $videoCallLog->id)
@section('heading','Call Log Detail')
@section('content')

<style>
    .detail-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
    .detail-card h3 { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f3f4f6; }
    .dl-grid { display: grid; grid-template-columns: 180px 1fr; gap: 10px 0; }
    .dl-label { font-size: 12px; color: #9ca3af; font-weight: 500; padding: 6px 0; text-transform: uppercase; letter-spacing: .04em; }
    .dl-value { font-size: 13px; color: #111827; padding: 6px 0; font-weight: 500; }
    .badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-orange { background: #ffedd5; color: #9a3412; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-gray   { background: #f3f4f6; color: #4b5563; }
    .avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0; }
    .timeline { position: relative; padding-left: 24px; }
    .timeline::before { content: ''; position: absolute; left: 7px; top: 8px; bottom: 8px; width: 2px; background: #e5e7eb; }
    .timeline-item { position: relative; margin-bottom: 16px; }
    .timeline-dot { position: absolute; left: -21px; top: 4px; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #d1d5db; }
    .timeline-dot.join { background: #16a34a; box-shadow: 0 0 0 2px #dcfce7; }
    .timeline-dot.leave { background: #dc2626; box-shadow: 0 0 0 2px #fee2e2; }
    .timeline-dot.start { background: #2563eb; box-shadow: 0 0 0 2px #dbeafe; }
    .timeline-dot.end { background: #7c3aed; box-shadow: 0 0 0 2px #ede9fe; }
    .timeline-time { font-size: 11px; color: #9ca3af; font-family: monospace; }
    .timeline-text { font-size: 13px; color: #374151; }
    .participant-row { display: flex; align-items: center; gap: 12px; padding: 12px; border: 1px solid #f3f4f6; border-radius: 10px; background: #fafafa; margin-bottom: 8px; }
    .duration-pill { font-size: 12px; font-weight: 600; color: #374151; background: #f3f4f6; padding: 3px 10px; border-radius: 6px; font-family: monospace; }
    .stat-mini { background: #f9fafb; border-radius: 10px; padding: 14px 18px; text-align: center; }
    .stat-mini-val { font-size: 22px; font-weight: 700; color: #111827; }
    .stat-mini-label { font-size: 11px; color: #9ca3af; margin-top: 3px; }
    .btn { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
    .btn-secondary { background: white; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background: #f9fafb; }
    .btn-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .star { color: #f59e0b; }
    .star-empty { color: #e5e7eb; }
    .meta-pre { font-family: monospace; font-size: 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; overflow-x: auto; color: #374151; white-space: pre-wrap; word-break: break-all; }
</style>

{{-- Back + Actions --}}
<div class="flex items-center justify-between mb-5">
    <a href="{{ route('admin.call-logs.index') }}" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Back to Logs
    </a>
    <div class="flex gap-2">
        @if($videoCallLog->is_recorded && $videoCallLog->recording_url)
        <a href="{{ $videoCallLog->recording_url }}" target="_blank" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="#dc2626" viewBox="0 0 16 16"><path d="M6 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM1 3a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/><path d="M9 6h.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 7.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 16H2a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h7z"/></svg>
            View Recording
        </a>
        @endif
        <form method="POST" action="{{ route('admin.call-logs.destroy', $videoCallLog) }}" onsubmit="return confirm('Delete this call log?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                Delete Log
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-3 gap-5">

    {{-- LEFT COLUMN --}}
    <div class="col-span-2 space-y-5">

        {{-- Overview --}}
        <div class="detail-card">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-base font-semibold text-gray-900">Call #{{ $videoCallLog->id }}</h2>
                        @php $sc = ['completed'=>'badge-green','ongoing'=>'badge-blue','initiated'=>'badge-yellow','missed'=>'badge-orange','failed'=>'badge-red','cancelled'=>'badge-gray']; @endphp
                        <span class="badge {{ $sc[$videoCallLog->status] ?? 'badge-gray' }}">{{ ucfirst($videoCallLog->status) }}</span>
                        @if($videoCallLog->is_recorded)
                        <span class="badge badge-red">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16"><circle cx="8" cy="8" r="8"/></svg>
                            Recorded
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 font-mono">{{ $videoCallLog->channel_name }}</p>
                </div>
                @php $provColors = ['agora'=>['#099DFD20','#099DFD'],'zoom'=>['#dbeafe','#2563eb'],'google'=>['#dcfce7','#16a34a']]; [$pbg,$pc] = $provColors[$videoCallLog->provider] ?? ['#f3f4f6','#6b7280']; @endphp
                <span class="badge text-sm px-3 py-1.5" style="background:{{ $pbg }};color:{{ $pc }};">{{ $videoCallLog->provider_label }}</span>
            </div>

            {{-- Stat mini row --}}
            <div class="grid grid-cols-4 gap-3 mb-5">
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $videoCallLog->duration_formatted ?: '—' }}</div>
                    <div class="stat-mini-label">Duration</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $videoCallLog->participants->count() }}</div>
                    <div class="stat-mini-label">Participants</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $videoCallLog->host_rating ? $videoCallLog->host_rating . '/5' : '—' }}</div>
                    <div class="stat-mini-label">Host Rating</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-val">{{ $videoCallLog->participant_rating ? $videoCallLog->participant_rating . '/5' : '—' }}</div>
                    <div class="stat-mini-label">Guest Rating</div>
                </div>
            </div>

            <div class="dl-grid">
                <div class="dl-label">Session ID</div>
                <div class="dl-value font-mono text-xs">{{ $videoCallLog->session_id ?? '—' }}</div>

                <div class="dl-label">Call Type</div>
                <div class="dl-value">{{ ucfirst($videoCallLog->call_type) }}</div>

                <div class="dl-label">Started At</div>
                <div class="dl-value">{{ $videoCallLog->started_at?->format('d M Y, H:i:s') ?? '—' }}</div>

                <div class="dl-label">Ended At</div>
                <div class="dl-value">{{ $videoCallLog->ended_at?->format('d M Y, H:i:s') ?? '—' }}</div>

                <div class="dl-label">End Reason</div>
                <div class="dl-value">{{ $videoCallLog->end_reason ? ucfirst(str_replace('_',' ',$videoCallLog->end_reason)) : '—' }}</div>

                @if($videoCallLog->booking_id)
                <div class="dl-label">Booking ID</div>
                <div class="dl-value">#{{ $videoCallLog->booking_id }}</div>
                @endif

                @if($videoCallLog->host_notes)
                <div class="dl-label">Notes</div>
                <div class="dl-value text-gray-600">{{ $videoCallLog->host_notes }}</div>
                @endif
            </div>
        </div>

        {{-- Participants --}}
        <div class="detail-card">
            <h3>Participants ({{ $videoCallLog->participants->count() }})</h3>
            @forelse($videoCallLog->participants as $p)
            <div class="participant-row">
                <div class="avatar" style="background:{{ $p->role === 'host' ? '#dbeafe' : '#f3e8ff' }};color:{{ $p->role === 'host' ? '#1e40af' : '#7c3aed' }};">
                    {{ strtoupper(substr($p->display_name ?? $p->user?->name ?? 'U', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm text-gray-900">{{ $p->display_name ?? $p->user?->name ?? 'Guest' }}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="badge {{ $p->role === 'host' ? 'badge-blue' : 'badge-gray' }}" style="font-size:10px;padding:1px 6px;">{{ ucfirst($p->role) }}</span>
                        @if($p->joined_at)
                        <span class="text-xs text-gray-400">Joined {{ $p->joined_at->format('H:i:s') }}</span>
                        @endif
                        @if($p->left_at)
                        <span class="text-xs text-gray-400">· Left {{ $p->left_at->format('H:i:s') }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="flex gap-1.5 items-center" title="{{ $p->mic_enabled ? 'Mic on' : 'Mic off' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="{{ $p->mic_enabled ? '#16a34a' : '#dc2626' }}" viewBox="0 0 16 16"><path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5z"/><path d="M10 8a2 2 0 1 1-4 0V3a2 2 0 0 1 4 0v5z"/></svg>
                    </div>
                    <div class="flex gap-1.5 items-center" title="{{ $p->camera_enabled ? 'Camera on' : 'Camera off' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="{{ $p->camera_enabled ? '#16a34a' : '#dc2626' }}" viewBox="0 0 16 16"><path d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/></svg>
                    </div>
                    @if($p->duration_seconds)
                    <span class="duration-pill">{{ $p->duration_formatted }}</span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">No participant records found.</p>
            @endforelse
        </div>

        {{-- Timeline --}}
        @if($videoCallLog->participants->count())
        <div class="detail-card">
            <h3>Call Timeline</h3>
            <div class="timeline">
                @if($videoCallLog->started_at)
                <div class="timeline-item">
                    <div class="timeline-dot start"></div>
                    <div class="timeline-time">{{ $videoCallLog->started_at->format('H:i:s') }}</div>
                    <div class="timeline-text font-medium">Call started</div>
                </div>
                @endif

                @foreach($videoCallLog->participants->sortBy('joined_at') as $p)
                @if($p->joined_at)
                <div class="timeline-item">
                    <div class="timeline-dot join"></div>
                    <div class="timeline-time">{{ $p->joined_at->format('H:i:s') }}</div>
                    <div class="timeline-text"><strong>{{ $p->display_name ?? $p->user?->name ?? 'Guest' }}</strong> joined as {{ $p->role }}</div>
                </div>
                @endif
                @if($p->left_at)
                <div class="timeline-item">
                    <div class="timeline-dot leave"></div>
                    <div class="timeline-time">{{ $p->left_at->format('H:i:s') }}</div>
                    <div class="timeline-text"><strong>{{ $p->display_name ?? $p->user?->name ?? 'Guest' }}</strong> left after {{ $p->duration_formatted }}</div>
                </div>
                @endif
                @endforeach

                @if($videoCallLog->ended_at)
                <div class="timeline-item">
                    <div class="timeline-dot end"></div>
                    <div class="timeline-time">{{ $videoCallLog->ended_at->format('H:i:s') }}</div>
                    <div class="timeline-text font-medium">Call ended — {{ $videoCallLog->duration_formatted }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Meta / Raw data --}}
        @if($videoCallLog->meta)
        <div class="detail-card">
            <h3>Provider Metadata</h3>
            <pre class="meta-pre">{{ json_encode($videoCallLog->meta, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="space-y-5">

        {{-- Host card --}}
        <div class="detail-card">
            <h3>Host</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="avatar" style="background:#dbeafe;color:#1e40af;width:48px;height:48px;font-size:16px;">
                    {{ strtoupper(substr($videoCallLog->host?->name ?? 'U', 0, 2)) }}
                </div>
                <div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $videoCallLog->host?->name ?? '—' }}</div>
                    <div class="text-xs text-gray-500">{{ $videoCallLog->host?->email ?? '' }}</div>
                </div>
            </div>
            @if($videoCallLog->host_rating)
            <div class="text-xs text-gray-500 mb-1">Host rating given</div>
            <div class="flex gap-1">
                @for($i=1;$i<=5;$i++)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="{{ $i <= $videoCallLog->host_rating ? '#f59e0b' : '#e5e7eb' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                @endfor
            </div>
            @endif
        </div>

        {{-- Participant card --}}
        @if($videoCallLog->participant)
        <div class="detail-card">
            <h3>Participant</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="avatar" style="background:#f3e8ff;color:#7c3aed;width:48px;height:48px;font-size:16px;">
                    {{ strtoupper(substr($videoCallLog->participant->name, 0, 2)) }}
                </div>
                <div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $videoCallLog->participant->name }}</div>
                    <div class="text-xs text-gray-500">{{ $videoCallLog->participant->email }}</div>
                </div>
            </div>
            @if($videoCallLog->participant_rating)
            <div class="text-xs text-gray-500 mb-1">Participant rating given</div>
            <div class="flex gap-1">
                @for($i=1;$i<=5;$i++)
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="{{ $i <= $videoCallLog->participant_rating ? '#f59e0b' : '#e5e7eb' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                @endfor
            </div>
            @endif
        </div>
        @endif

        {{-- Recording --}}
        @if($videoCallLog->is_recorded)
        <div class="detail-card">
            <h3>Recording</h3>
            @if($videoCallLog->recording_url)
            <a href="{{ $videoCallLog->recording_url }}" target="_blank" class="flex items-center gap-2 text-blue-600 text-sm hover:underline mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M6 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM1 3a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/><path d="M9 6h.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 7.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 16H2a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h7z"/></svg>
                Open Recording
            </a>
            @endif
            @if($videoCallLog->recording_size_kb)
            <div class="text-xs text-gray-500">Size: {{ number_format($videoCallLog->recording_size_kb / 1024, 1) }} MB</div>
            @endif
        </div>
        @endif

        {{-- Quick info --}}
        <div class="detail-card">
            <h3>Quick Info</h3>
            <div class="space-y-2">
                @foreach([
                    ['Created', $videoCallLog->created_at->format('d M Y, H:i')],
                    ['Updated', $videoCallLog->updated_at->format('d M Y, H:i')],
                    ['Log ID', '#' . $videoCallLog->id],
                ] as [$l, $v])
                <div class="flex justify-between items-center text-xs">
                    <span class="text-gray-400 uppercase tracking-wide font-medium">{{ $l }}</span>
                    <span class="text-gray-700 font-medium font-mono">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@endsection
