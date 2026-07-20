<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\SessionReview;
use Illuminate\Http\Request;
 
class SessionReviewController extends Controller
{
    /**
     * Show review form for a completed session.
     */
    public function create(ConsultationSession $session)
    {
        $this->authorizeReview($session);
 
        $role = $this->getUserRole($session);
        $existingReview = $session->reviews()
            ->where('reviewer_id', auth()->id())
            ->first();
 
        if ($existingReview) {
            return redirect()->route('sessions.show', $session)
                ->with('info', 'You have already submitted a review for this session.');
        }
 
        return view('sessions.review', compact('session', 'role'));
    }
 
    /**
     * Store the review.
     */
    public function store(Request $request, ConsultationSession $session)
    {
        $this->authorizeReview($session);
 
        $role = $this->getUserRole($session);
 
        // Prevent duplicate reviews
        if ($session->reviews()->where('reviewer_id', auth()->id())->exists()) {
            return redirect()->route('sessions.show', $session)
                ->with('error', 'You have already reviewed this session.');
        }
 
        $data = $request->validate([
            'overall_rating'        => 'required|integer|between:1,5',
            'communication_rating'  => 'nullable|integer|between:1,5',
            'knowledge_rating'      => 'nullable|integer|between:1,5',
            'punctuality_rating'    => 'nullable|integer|between:1,5',
            'helpfulness_rating'    => 'nullable|integer|between:1,5',
            'review_text'           => 'nullable|string|max:2000',
            'would_recommend'       => 'nullable|boolean',
            'is_public'             => 'nullable|boolean',
        ]);
 
        $revieweeId = $role === 'mentee' ? $session->mentor_id : $session->mentee_id;
 
        SessionReview::create(array_merge($data, [
            'session_id'      => $session->id,
            'reviewer_id'     => auth()->id(),
            'reviewee_id'     => $revieweeId,
            'reviewer_role'   => $role,
            'would_recommend' => $request->boolean('would_recommend', true),
            'is_public'       => $request->boolean('is_public', true),
            'submitted_at'    => now(),
        ]));
 
        if ($role === 'mentee' && $session->mentor) {
            $session->mentor->recalculateRating();
        }
 
        return redirect()->route('sessions.show', $session)
            ->with('success', 'Thank you for your review!');
    }
 
    /**
     * Admin: view all reviews for a session
     */
    public function sessionReviews(ConsultationSession $session)
    {
        $session->load(['reviews.reviewer', 'reviews.reviewee', 'mentor', 'mentee']);
        return view('admin.sessions.reviews', compact('session'));
    }
 
    private function authorizeReview(ConsultationSession $session): void
    {
        abort_unless($session->status === 'completed', 403, 'Reviews are only allowed for completed sessions.');
        abort_unless(
            in_array(auth()->id(), [$session->mentor_id, $session->mentee_id]),
            403, 'You are not a participant of this session.'
        );
    }
 
    private function getUserRole(ConsultationSession $session): string
    {
        return auth()->id() === $session->mentor_id ? 'mentor' : 'mentee';
    }
}