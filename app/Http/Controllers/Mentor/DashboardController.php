<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\WalletTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        $mentor = auth()->user();

        $upcomingSessions = ConsultationSession::where('mentor_id', $mentor->id)
            ->with('mentee')
            ->whereIn('status', ['pending','confirmed'])
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $recentReviews = [];/* $mentor->reviewsReceived()
            ->with('reviewer')
            ->where('is_public', true)
            ->latest('submitted_at')
            ->limit(5)
            ->get(); */

        $stats = [
            'total_sessions'       => $mentor->total_sessions,
            'this_month_sessions'  => ConsultationSession::where('mentor_id', $mentor->id)
                                        ->where('status','completed')
                                        ->whereMonth('updated_at', now()->month)
                                        ->count(),
            'total_earnings'       => WalletTransaction::where('user_id', $mentor->id)
                                        ->where('type','credit')
                                        ->sum('amount'),
            'this_month_earnings'  => WalletTransaction::where('user_id', $mentor->id)
                                        ->where('type','credit')
                                        ->whereMonth('created_at', now()->month)
                                        ->sum('amount'),
            'active_mentees'       => ConsultationSession::where('mentor_id', $mentor->id)
                                        ->where('status','completed')
                                        ->distinct('mentee_id')
                                        ->count('mentee_id'),
            'pending_sessions'     => ConsultationSession::where('mentor_id', $mentor->id)
                                        ->where('status','pending')
                                        ->count(),
        ];

        return view('frontend.mentors.dashboard', compact('upcomingSessions','recentReviews','stats'));
    }
}