<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('causer')->latest('logged_at');
 
        // Search
        if ($search = $request->search) {
            $query->search($search);
        }
 
        // Filters
        if ($module = $request->module)   $query->byModule($module);
        if ($level  = $request->level)    $query->byLevel($level);
        if ($event  = $request->event)    $query->byEvent($event);
        if ($userId = $request->user_id)  $query->forCauser($userId);
        if ($from   = $request->date_from) $query->whereDate('logged_at', '>=', $from);
        if ($to     = $request->date_to)   $query->whereDate('logged_at', '<=', $to);
 
        $logs = $query->paginate(50)->withQueryString();
 
        // Stats (last 24h)
        $stats = [
            'total_today'   => ActivityLog::whereDate('logged_at', today())->count(),
            'warnings'      => ActivityLog::whereDate('logged_at', today())->where('level', 'warning')->count(),
            'errors'        => ActivityLog::whereDate('logged_at', today())->where('level', 'danger')->count(),
            'logins_today'  => ActivityLog::whereDate('logged_at', today())->where('event', 'login')->count(),
            'total_all'     => ActivityLog::count(),
            'unique_users'  => ActivityLog::whereNotNull('causer_id')->distinct('causer_id')->count(),
        ];
 
        // Module breakdown for chart
        $moduleBreakdown = ActivityLog::select('module', DB::raw('count(*) as count'))
            ->whereDate('logged_at', '>=', now()->subDays(7))
            ->groupBy('module')
            ->orderByDesc('count')
            ->get();
 
        // Level breakdown
        $levelBreakdown = ActivityLog::select('level', DB::raw('count(*) as count'))
            ->whereDate('logged_at', '>=', now()->subDays(7))
            ->groupBy('level')
            ->get()
            ->pluck('count', 'level');
 
        // Filter options
        $modules = ActivityLog::distinct()->whereNotNull('module')->pluck('module')->sort()->values();
        $events  = ActivityLog::distinct()->whereNotNull('event')->pluck('event')->sort()->values();
        $users   = User::whereIn('id', ActivityLog::distinct()->whereNotNull('causer_id')->pluck('causer_id'))
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
 
        return view('admin.logs.index', compact(
            'logs', 'stats', 'modules', 'events', 'users',
            'moduleBreakdown', 'levelBreakdown'
        ));
    }
 
    public function show(ActivityLog $log)
    {
        $log->load('causer');
        return view('admin.logs.show', compact('log'));
    }
 
    public function export(Request $request)
    {
        $query = ActivityLog::latest('logged_at');
        if ($request->module)    $query->byModule($request->module);
        if ($request->level)     $query->byLevel($request->level);
        if ($request->event)     $query->byEvent($request->event);
        if ($request->date_from) $query->whereDate('logged_at', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('logged_at', '<=', $request->date_to);
 
        $logs = $query->limit(10000)->get();
 
        $csv = "ID,Date,User,Event,Module,Level,Description,IP Address,URL\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                '"' . $log->logged_at->format('Y-m-d H:i:s') . '"',
                '"' . ($log->causer_name ?? 'System') . '"',
                '"' . $log->event . '"',
                '"' . ($log->module ?? '') . '"',
                '"' . $log->level . '"',
                '"' . str_replace('"', '""', $log->description) . '"',
                '"' . ($log->ip_address ?? '') . '"',
                '"' . ($log->url ?? '') . '"',
            ]) . "\n";
        }
 
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity_logs_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
 
    public function destroy(ActivityLog $log)
    {
        $log->delete();
        return redirect()->back()->with('success', 'Log entry deleted.');
    }
 
    public function bulkDestroy(Request $request)
    {
        $request->validate(['action' => 'required|in:delete_filtered,delete_older_30,delete_older_90,delete_all']);
 
        $count = match ($request->action) {
            'delete_older_30'  => ActivityLog::where('logged_at', '<', now()->subDays(30))->count(),
            'delete_older_90'  => ActivityLog::where('logged_at', '<', now()->subDays(90))->count(),
            'delete_all'       => ActivityLog::count(),
            default            => 0,
        };
 
        match ($request->action) {
            'delete_older_30' => ActivityLog::where('logged_at', '<', now()->subDays(30))->delete(),
            'delete_older_90' => ActivityLog::where('logged_at', '<', now()->subDays(90))->delete(),
            'delete_all'      => ActivityLog::truncate(),
            default           => null,
        };
 
        return redirect()->back()->with('success', "Deleted {$count} log entries.");
    }
 
    // ── API: live tail ─────────────────────────────────────────
    public function latest(Request $request)
    {
        $since = $request->since ? \Carbon\Carbon::parse($request->since) : now()->subMinutes(1);
        $logs  = ActivityLog::where('logged_at', '>', $since)
            ->latest('logged_at')
            ->limit(20)
            ->get(['id','event','module','level','description','causer_name','ip_address','logged_at']);
 
        return response()->json($logs);
    }
}