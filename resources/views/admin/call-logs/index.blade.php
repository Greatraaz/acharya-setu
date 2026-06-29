@extends('admin.layouts.app')
@section('title','Video Call Logs')
@section('heading','Video Call Logs')
@section('content')

<style>
    .form-input { border: 1px solid #d1d5db; border-radius: 8px; padding: 7px 12px; font-size: 13px; color: #111827; background: white; outline: none; transition: border-color .2s, box-shadow .2s; }
    .form-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px #dbeafe; }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }
    .stat-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; }
    .stat-val { font-size: 26px; font-weight: 700; color: #111827; line-height: 1; }
    .stat-label { font-size: 12px; color: #9ca3af; margin-top: 4px; font-weight: 500; letter-spacing: .03em; text-transform: uppercase; }
    .stat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .badge { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-yellow { background: #fef9c3; color: #854d0e; }
    .badge-orange { background: #ffedd5; color: #9a3412; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-gray   { background: #f3f4f6; color: #4b5563; }
    .badge-purple { background: #ede9fe; color: #5b21b6; }
    .provider-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .btn { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 6px; }
    .btn-primary { background: #2563eb; color: white; } .btn-primary:hover { background: #1d4ed8; }
    .btn-secondary { background: white; color: #374151; border: 1px solid #d1d5db; } .btn-secondary:hover { background: #f9fafb; }
    .btn-danger { background: #fee2e2; color: #991b1b; } .btn-danger:hover { background: #fecaca; }
    .btn-sm { padding: 5px 12px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    thead th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
    thead th:first-child { border-radius: 8px 0 0 0; }
    thead th:last-child { border-radius: 0 8px 0 0; }
    tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
    tbody tr:hover { background: #f9fafb; }
    tbody td { padding: 12px 14px; font-size: 13px; color: #374151; vertical-align: middle; }
    .duration-pill { font-size: 12px; font-weight: 600; color: #374151; background: #f3f4f6; padding: 2px 8px; border-radius: 6px; font-family: monospace; }
    .avatar { width: 30px; height: 30px; border-radius: 50%; background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .user-cell { display: flex; align-items: center; gap: 8px; }
    .user-name { font-size: 13px; font-weight: 500; color: #111827; }
    .user-role { font-size: 11px; color: #9ca3af; }
    .recording-icon { color: #dc2626; }
    .filter-bar { background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; }
    .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .table-card { background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .pagination-wrap { padding: 14px 18px; border-top: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
    .chart-bar { height: 6px; border-radius: 3px; background: #2563eb; transition: width .4s; }
    select.form-input { min-width: 130px; }
</style>

<div class="space-y-5">

    {{-- ── Stats Row ── --}}
    <div class="grid grid-cols-6 gap-4">
        @php
        $statCards = [
            ['Total Calls',    $stats['total'],           '#dbeafe','#2563eb', 'M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z'],
            ['Completed',      $stats['completed'],       '#dcfce7','#16a34a', 'M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z'],
            ['Ongoing',        $stats['ongoing'],         '#ede9fe','#7c3aed', 'M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16z'],
            ['Missed/Failed',  $stats['missed'],          '#fee2e2','#dc2626', 'M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z'],
            ['Total Minutes',  number_format($stats['total_minutes']), '#fef9c3','#d97706', 'M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16z'],
            ['Avg Duration',   gmdate('i:s', $stats['avg_duration']), '#f0fdf4','#059669', 'M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16z'],
        ];
        @endphp
        @foreach($statCards as [$label, $value, $bg, $color, $icon])
        <div class="stat-card flex items-center gap-3">
            <div class="stat-icon" style="background:{{ $bg }};">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="{{ $color }}" viewBox="0 0 16 16"><path d="{{ $icon }}"/></svg>
            </div>
            <div>
                <div class="stat-val" style="font-size:20px;">{{ $value }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.call-logs.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" class="form-input" style="width:220px;" placeholder="Channel, session, name…" value="{{ request('search') }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="form-input form-select">
                    <option value="">All Statuses</option>
                    @foreach(['initiated'=>'Initiated','ongoing'=>'Ongoing','completed'=>'Completed','missed'=>'Missed','failed'=>'Failed','cancelled'=>'Cancelled'] as $val=>$label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Provider</label>
                <select name="provider" class="form-input form-select">
                    <option value="">All Providers</option>
                    <option value="agora"  {{ request('provider') === 'agora'  ? 'selected' : '' }}>Agora</option>
                    <option value="zoom"   {{ request('provider') === 'zoom'   ? 'selected' : '' }}>Zoom</option>
                    <option value="google" {{ request('provider') === 'google' ? 'selected' : '' }}>Google Meet</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>
                    Filter
                </button>
                <a href="{{ route('admin.call-logs.index') }}" class="btn btn-secondary">Reset</a>
            </div>
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.call-logs.export', request()->all()) }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                    Export CSV
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="table-card">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-semibold text-gray-800">Call Logs</h3>
                <span class="badge badge-gray">{{ $logs->total() }} records</span>
            </div>
            <form method="POST" action="{{ route('admin.call-logs.bulk-destroy') }}" id="bulk-form">
                @csrf @method('DELETE')
                <input type="hidden" name="ids" id="bulk-ids">
                <button type="button" onclick="bulkDelete()" class="btn btn-danger btn-sm hidden" id="bulk-delete-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                    Delete Selected
                </button>
            </form>
        </div>

        @if($logs->isEmpty())
        <div class="empty-state">
            <svg class="mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16"><path d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/></svg>
            <p class="text-sm font-medium text-gray-500 mb-1">No call logs found</p>
            <p class="text-xs text-gray-400">Call logs will appear here once video calls are made.</p>
        </div>
        @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="select-all" onchange="toggleAll(this)" class="rounded"></th>
                        <th>#</th>
                        <th>Host</th>
                        <th>Participant</th>
                        <th>Provider</th>
                        <th>Channel / Session</th>
                        <th>Status</th>
                        <th>Started At</th>
                        <th>Duration</th>
                        <th>Rec</th>
                        <th>Rating</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr>
                        <td><input type="checkbox" class="row-check rounded" value="{{ $log->id }}" onchange="updateBulk()"></td>
                        <td class="text-gray-400 text-xs font-mono">#{{ $log->id }}</td>

                        {{-- Host --}}
                        <td>
                            <div class="user-cell">
                                <div class="avatar">{{ strtoupper(substr($log->host->name ?? 'U', 0, 2)) }}</div>
                                <div>
                                    <div class="user-name">{{ $log->host->name ?? '—' }}</div>
                                    <div class="user-role">Host</div>
                                </div>
                            </div>
                        </td>

                        {{-- Participant --}}
                        <td>
                            @if($log->participant)
                            <div class="user-cell">
                                <div class="avatar" style="background:#f3e8ff;color:#7c3aed;">{{ strtoupper(substr($log->participant->name, 0, 2)) }}</div>
                                <div>
                                    <div class="user-name">{{ $log->participant->name }}</div>
                                    <div class="user-role">Participant</div>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Provider --}}
                        <td>
                            @php
                            $provColors = ['agora'=>['#099DFD20','#099DFD'],'zoom'=>['#dbeafe','#2563eb'],'google'=>['#dcfce7','#16a34a']];
                            [$pbg, $pc] = $provColors[$log->provider] ?? ['#f3f4f6','#6b7280'];
                            @endphp
                            <span class="badge" style="background:{{ $pbg }};color:{{ $pc }};">
                                <span class="provider-dot" style="background:{{ $pc }};"></span>
                                {{ $log->provider_label }}
                            </span>
                        </td>

                        {{-- Channel --}}
                        <td>
                            <div class="font-mono text-xs text-gray-700 truncate" style="max-width:130px;" title="{{ $log->channel_name }}">{{ $log->channel_name }}</div>
                            @if($log->session_id)
                            <div class="text-xs text-gray-400 font-mono truncate" style="max-width:130px;">{{ $log->session_id }}</div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @php
                            $sc = ['completed'=>'badge-green','ongoing'=>'badge-blue','initiated'=>'badge-yellow','missed'=>'badge-orange','failed'=>'badge-red','cancelled'=>'badge-gray'];
                            @endphp
                            <span class="badge {{ $sc[$log->status] ?? 'badge-gray' }}">
                                @if($log->status === 'ongoing')
                                <span class="provider-dot" style="background:#2563eb;animation:pulse 1.5s infinite;"></span>
                                @endif
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>

                        {{-- Started At --}}
                        <td>
                            @if($log->started_at)
                            <div class="text-xs text-gray-700">{{ $log->started_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $log->started_at->format('H:i:s') }}</div>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Duration --}}
                        <td>
                            @if($log->duration_seconds)
                            <span class="duration-pill">{{ $log->duration_formatted }}</span>
                            @elseif($log->status === 'ongoing')
                            <span class="badge badge-blue" id="live-timer-{{ $log->id }}" data-started="{{ $log->started_at?->timestamp }}">Live</span>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Recording --}}
                        <td>
                            @if($log->is_recorded && $log->recording_url)
                            <a href="{{ $log->recording_url }}" target="_blank" title="View recording" class="recording-icon flex justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M6 3a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM1 3a2 2 0 1 0 4 0 2 2 0 0 0-4 0z"/><path d="M9 6h.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 7.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 16H2a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h7z"/></svg>
                            </a>
                            @elseif($log->is_recorded)
                            <span class="text-xs text-gray-400">Saved</span>
                            @else
                            <span class="text-gray-300 flex justify-center">—</span>
                            @endif
                        </td>

                        {{-- Rating --}}
                        <td>
                            @if($log->host_rating || $log->participant_rating)
                            <div class="flex flex-col gap-0.5">
                                @if($log->host_rating)
                                <div class="flex gap-0.5 items-center">
                                    @for($i=1;$i<=5;$i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="{{ $i <= $log->host_rating ? '#f59e0b' : '#d1d5db' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                    @endfor
                                    <span class="text-xs text-gray-400 ml-1">H</span>
                                </div>
                                @endif
                                @if($log->participant_rating)
                                <div class="flex gap-0.5 items-center">
                                    @for($i=1;$i<=5;$i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="{{ $i <= $log->participant_rating ? '#f59e0b' : '#d1d5db' }}" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                    @endfor
                                    <span class="text-xs text-gray-400 ml-1">P</span>
                                </div>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.call-logs.show', $log) }}" class="btn btn-secondary btn-sm" title="View details">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.call-logs.destroy', $log) }}" onsubmit="return confirm('Delete this call log?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrap">
            <div class="text-xs text-gray-500">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} results
            </div>
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    {{-- Provider distribution mini chart --}}
    @if(!empty($stats['by_provider']) && $stats['total'] > 0)
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Calls by Provider</h3>
        <div class="space-y-3">
            @foreach($stats['by_provider'] as $provider => $count)
            @php $pct = round($count / $stats['total'] * 100); @endphp
            <div class="flex items-center gap-3">
                <div class="text-xs text-gray-600 font-medium" style="width:90px;">{{ ucfirst($provider) }}</div>
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ ['agora'=>'#099DFD','zoom'=>'#2563eb','google'=>'#16a34a'][$provider] ?? '#6b7280' }};"></div>
                </div>
                <div class="text-xs text-gray-500 font-mono" style="width:50px;text-align:right;">{{ $count }} ({{ $pct }}%)</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>

<script>
// Live timer for ongoing calls
document.querySelectorAll('[id^="live-timer-"]').forEach(el => {
    const started = parseInt(el.dataset.started);
    if (!started) return;
    setInterval(() => {
        const elapsed = Math.floor(Date.now() / 1000) - started;
        const h = Math.floor(elapsed / 3600);
        const m = Math.floor((elapsed % 3600) / 60);
        const s = elapsed % 60;
        el.textContent = h > 0
            ? `${h}h ${String(m).padStart(2,'0')}m`
            : `${m}m ${String(s).padStart(2,'0')}s`;
    }, 1000);
});

// Bulk selection
function toggleAll(master) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    updateBulk();
}
function updateBulk() {
    const checked = [...document.querySelectorAll('.row-check:checked')];
    document.getElementById('bulk-delete-btn').classList.toggle('hidden', checked.length === 0);
}
function bulkDelete() {
    const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    if (!ids.length || !confirm(`Delete ${ids.length} call log(s)?`)) return;
    document.getElementById('bulk-ids').value = JSON.stringify(ids);
    document.getElementById('bulk-form').submit();
}
</script>

@endsection
