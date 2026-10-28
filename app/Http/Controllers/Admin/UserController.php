<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\MenteeOnboardingService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EducationStream;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function menteeIndex(Request $request)
    {
        $mentees = $this->menteeListingQuery($request)
            ->paginate(20)
            ->withQueryString();

        return view('admin.mentees.index', compact('mentees'));
    }

    public function menteeExport(Request $request): StreamedResponse
    {
        $headers = [
            'Name', 'Email', 'Phone', 'College', 'Field', 'Year',
            'Assigned Mentor', 'Plan', 'Onboarding', 'Status', 'Joined',
        ];

        return $this->streamFilteredExport($request, 'mentees', $headers, function ($out, $isExcel) use ($request) {
            $this->menteeListingQuery($request)
                ->reorder()
                ->chunkById(200, function ($rows) use ($out, $isExcel) {
                    foreach ($rows as $mentee) {
                        $plan = $mentee->subscriptions->first();
                        $values = [
                            $mentee->name,
                            $mentee->email,
                            $mentee->phone ?: '',
                            $mentee->college ?: '',
                            $mentee->field ?: '',
                            $mentee->year ?: '',
                            $mentee->assignedMentor?->name ?: 'Unassigned',
                            $plan?->plan?->name ?? 'Free',
                            $mentee->onboarding_completed
                                ? 'Complete'
                                : 'Step '.$mentee->onboarding_step.'/4',
                            $mentee->is_active ? 'Active' : 'Inactive',
                            optional($mentee->created_at)->format('Y-m-d H:i'),
                        ];
                        $this->writeExportRow($out, $values, $isExcel);
                    }
                });
        });
    }
 
    public function menteeShow(User $mentee, MenteeOnboardingService $onboarding)
    {
        $mentee->load(['assignedMentor', 'menteeSessions.mentor', 'enrollments.stream']);
        $tracks = $onboarding->menteeTracks($mentee->id);
        $preferences = $mentee->preferences ?? [];

        return view('admin.mentees.show', compact('mentee', 'tracks', 'preferences'));
    }

    public function menteeJourney(User $mentee)
    {
        abort_unless($mentee->role === 'mentee', 404);

        return redirect()->route('admin.curriculum.streams', [
            'mentee_id' => $mentee->id,
        ]);
    }
 
    public function menteeToggleStatus(User $mentee)
    {
        $mentee->update(['is_active' => !$mentee->is_active]);
        ActivityLogger::record(
            'user_status_changed',
            auth()->user()->name . " set mentee {$mentee->name} to " . ($mentee->is_active ? 'active' : 'inactive'),
            'users', 'info'
        );
        return redirect()->back()->with('success', "Mentee status updated.");
    }
 
    public function menteeAssignMentor(Request $request, User $mentee)
    {
        $request->validate(['mentor_id' => 'nullable|exists:users,id']);
 
        $old = $mentee->assigned_mentor_id;
        $mentee->update(['assigned_mentor_id' => $request->mentor_id ?: null]);

        if ($request->mentor_id) {
            EducationStream::where('mentee_id', $mentee->id)
                ->update(['mentor_id' => $request->mentor_id]);
        }
 
        $mentor = $request->mentor_id ? User::find($request->mentor_id) : null;
        ActivityLogger::record(
            'mentor_assigned',
            auth()->user()->name . " assigned mentor " . ($mentor?->name ?? 'none') . " to mentee {$mentee->name}",
            'users', 'info'
        );
 
        return redirect()->back()->with('success', $mentor ? "Mentor assigned to {$mentee->name}." : "Mentor unassigned.");
    }
 
    public function menteeDestroy(User $mentee)
    {
        ActivityLogger::record('user_deleted', auth()->user()->name . " deleted mentee: {$mentee->name}", 'users', 'danger');
        $mentee->delete();
        return redirect()->route('admin.mentees.index')->with('success', "{$mentee->name} has been deleted.");
    }
 
    public function menteeTrashed(Request $request)
    {
        $users = User::where('role', 'mentee')
            ->onlyTrashed()
            ->when($request->search, fn($q) =>
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('deleted_email', 'like', "%{$request->search}%")
                )
            )
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.users.trashed', ['users' => $users, 'type' => 'mentee']);
    }
 
    public function restore(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $emailRestored = $user->restoreAccount();
        ActivityLogger::record('user_restored', auth()->user()->name . " restored user: {$user->name}", 'users', 'success');

        if (! $emailRestored) {
            return redirect()->back()->with(
                'warning',
                "{$user->name} has been restored, but their original email is already in use by another account."
            );
        }

        return redirect()->back()->with('success', "{$user->name} has been restored.");
    }
 
    public function forceDelete(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        ActivityLogger::record('user_force_deleted', auth()->user()->name . " permanently deleted: {$user->name}", 'users', 'danger');
        $user->forceDelete();
        return redirect()->back()->with('success', "User permanently deleted.");
    }
 
    // ── MENTOR ROUTES ─────────────────────────────────────────
 
    public function mentorIndex(Request $request)
    {
        $mentors = $this->mentorListingQuery($request)
            ->with('assignedMentees')
            ->paginate(20)
            ->withQueryString();

        return view('admin.mentors.index', compact('mentors'));
    }

    public function mentorExport(Request $request): StreamedResponse
    {
        $headers = [
            'Name', 'Email', 'Phone', 'Designation', 'Company', 'Field',
            'Experience (years)', 'Rate/min', 'Hourly Rate', 'Mentees',
            'Rating', 'Sessions', 'Mentor Status', 'Account', 'Pending Changes', 'Joined',
        ];

        return $this->streamFilteredExport($request, 'mentors', $headers, function ($out, $isExcel) use ($request) {
            $this->mentorListingQuery($request)
                ->withCount('assignedMentees')
                ->reorder()
                ->chunkById(200, function ($rows) use ($out, $isExcel) {
                    foreach ($rows as $mentor) {
                        $rate = (float) $mentor->rate_per_minute;
                        $values = [
                            $mentor->name,
                            $mentor->email,
                            $mentor->phone ?: '',
                            $mentor->designation ?: '',
                            $mentor->company ?: '',
                            $mentor->field ?: '',
                            $mentor->experience_years ?? 0,
                            $rate > 0 ? $rate : 'Free',
                            $rate > 0 ? round($rate * 60, 2) : 'Free',
                            $mentor->assigned_mentees_count ?? 0,
                            $mentor->rating > 0 ? number_format((float) $mentor->rating, 1) : '',
                            $mentor->total_sessions ?? 0,
                            ucfirst((string) $mentor->mentor_status),
                            $mentor->is_active ? 'Active' : 'Inactive',
                            $mentor->has_pending_changes ? 'Yes' : 'No',
                            optional($mentor->created_at)->format('Y-m-d H:i'),
                        ];
                        $this->writeExportRow($out, $values, $isExcel);
                    }
                });
        });
    }
 
    public function mentorToggleStatus(User $mentor)
    {
        $mentor->update(['is_active' => !$mentor->is_active]);
        ActivityLogger::record(
            'mentor_status_changed',
            auth()->user()->name . " set mentor {$mentor->name} to " . ($mentor->is_active ? 'active' : 'inactive'),
            'users', 'info'
        );
        return redirect()->back()->with('success', "Mentor status updated.");
    }
 
    public function mentorTrashed(Request $request)
    {
        $users = User::where('role', 'mentor')
            ->onlyTrashed()
            ->when($request->search, fn($q) =>
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('deleted_email', 'like', "%{$request->search}%")
                )
            )
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.users.trashed', ['users' => $users, 'type' => 'mentor']);
    }

    private function mentorListingQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        return User::query()
            ->where('role', 'mentor')
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($request->filled('mentor_status'), fn ($q) => $q->where('mentor_status', $request->mentor_status))
            ->when($request->filled('field'), fn ($q) => $q->where('field', 'like', '%'.$request->field.'%'))
            ->when($request->pending_changes === '1', fn ($q) => $q->where('has_pending_changes', true))
            ->whereNull('deleted_at')
            ->latest();
    }

    private function menteeListingQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $activeSubscription = fn ($q) => $q
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', now());

        return User::query()
            ->where('role', 'mentee')
            ->with([
                'assignedMentor:id,name',
                'subscriptions' => fn ($q) => $activeSubscription($q)
                    ->with('plan:id,name')
                    ->latest('starts_at'),
            ])
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('college', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->onboarded === '1', fn ($q) => $q->where('onboarding_completed', true))
            ->when($request->onboarded === '0', fn ($q) => $q->where('onboarding_completed', false))
            ->when($request->assigned === 'yes', fn ($q) => $q->whereNotNull('assigned_mentor_id'))
            ->when($request->assigned === 'no', fn ($q) => $q->whereNull('assigned_mentor_id'))
            ->when($request->subscribed === 'yes', fn ($q) => $q->whereHas('subscriptions', $activeSubscription))
            ->when($request->subscribed === 'no', fn ($q) => $q->whereDoesntHave('subscriptions', $activeSubscription))
            ->whereNull('deleted_at')
            ->latest();
    }

    /**
     * @param  callable(resource, bool): void  $writeRows
     */
    private function streamFilteredExport(Request $request, string $basename, array $headers, callable $writeRows): StreamedResponse
    {
        $isExcel = $request->input('format') === 'excel';
        $filename = $basename.'-'.now()->format('Ymd-His').($isExcel ? '.xls' : '.csv');
        $contentType = $isExcel
            ? 'application/vnd.ms-excel; charset=UTF-8'
            : 'text/csv; charset=UTF-8';

        return response()->streamDownload(function () use ($headers, $writeRows, $isExcel) {
            $out = fopen('php://output', 'w');

            if ($isExcel) {
                fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
                fwrite($out, '<?mso-application progid="Excel.Sheet"?>'."\n");
                fwrite($out, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Export"><Table>'."\n");
                $this->writeExportRow($out, $headers, true);
                $writeRows($out, true);
                fwrite($out, '</Table></Worksheet></Workbook>');
            } else {
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, $headers);
                $writeRows($out, false);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * @param  resource  $out
     * @param  list<mixed>  $values
     */
    private function writeExportRow($out, array $values, bool $isExcel): void
    {
        if (! $isExcel) {
            fputcsv($out, $values);

            return;
        }

        fwrite($out, '<Row>');
        foreach ($values as $value) {
            $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            fwrite($out, '<Cell><Data ss:Type="String">'.$text.'</Data></Cell>');
        }
        fwrite($out, "</Row>\n");
    }
}
