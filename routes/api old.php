<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MentorsController;
use App\Http\Controllers\Api\SessionsController;
use App\Http\Controllers\Api\TasksController;
use App\Http\Controllers\Api\AssessmentsController;
use App\Http\Controllers\Api\VideosController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\CareerTracksController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\JobsController;
use App\Http\Controllers\Api\QuizzesController;
use App\Http\Controllers\Api\Mentor\AvailabilityController;
use App\Http\Controllers\Api\ReviewsController;
use App\Http\Controllers\Api\CallsController;
use App\Http\Controllers\Api\WellnessController;
use App\Http\Controllers\Api\ReferralsController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\PlansController;
use App\Http\Controllers\Api\AssignmentsController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Mentee\OnboardingController as MenteeOnboarding;
use App\Http\Controllers\Api\Mentor\OnboardingController as MentorOnboarding;

// Health
// Health check endpoints
Route::prefix('v1')->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => 'API Working']);
    });
    Route::get('/health', function (Request $request) {
        return response()->json([
            'status' => 'ok',
            'app'    => 'Acharya Setu API',
            'version'=> '1.0.0'
        ]);
    });

    
});

// Public Auth
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Protected
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    

    Route::prefix('auth')->group(function () {
        Route::get('/me',               [AuthController::class, 'me']);
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::patch('/profile',        [AuthController::class, 'updateProfile']);
        Route::post('/upload-photo',    [AuthController::class, 'uploadPhoto']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::patch('/onboarding',     [AuthController::class, 'updateOnboarding']);
    });

    // Mentors
    Route::get('/mentors',                  [MentorsController::class, 'index']);
    Route::get('/mentors/{id}',             [MentorsController::class, 'show']);
    Route::get('/mentors/{id}/availability',[MentorsController::class, 'availability']);

    // Sessions
    Route::get('/sessions',       [SessionsController::class, 'index']);
    Route::post('/sessions',      [SessionsController::class, 'store']);
    Route::patch('/sessions/{id}',[SessionsController::class, 'update']);
    Route::delete('/sessions/{id}',[SessionsController::class,'destroy']);

    // Tasks
    Route::get('/tasks',       [TasksController::class, 'index']);
    Route::post('/tasks',      [TasksController::class, 'store']);
    Route::patch('/tasks/{id}',[TasksController::class, 'update']);
    Route::delete('/tasks/{id}',[TasksController::class,'destroy']);

    // Assessments
    Route::get('/assessments',              [AssessmentsController::class, 'index']);
    Route::get('/assessments/{id}',         [AssessmentsController::class, 'show']);
    Route::post('/assessments/{id}/submit', [AssessmentsController::class, 'submit']);

    // Videos
    Route::get('/videos',                [VideosController::class, 'index']);
    Route::post('/videos',               [VideosController::class, 'store']);
    Route::post('/videos/{id}/watched',  [VideosController::class, 'markWatched']);

    // Wallet
    Route::get('/wallet/balance',      [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/topup',       [WalletController::class, 'topup']);

    // Notifications
    Route::get('/notifications',                  [NotificationsController::class, 'index']);
    Route::patch('/notifications/{id}/read',      [NotificationsController::class, 'markRead']);
    Route::post('/notifications/mark-all-read',   [NotificationsController::class, 'markAllRead']);

    // Career tracks
    Route::get('/career-tracks',                                  [CareerTracksController::class, 'index']);
    Route::post('/career-tracks',                                 [CareerTracksController::class, 'store']);
    Route::patch('/career-tracks/milestones/{id}/complete',       [CareerTracksController::class, 'updateMilestone']);

    // Community
    Route::get('/community/channels',                      [CommunityController::class, 'channels']);
    Route::get('/community/channels/{channelId}/messages', [CommunityController::class, 'messages']);
    Route::post('/community/channels/{channelId}/messages',[CommunityController::class, 'postMessage']);
    Route::post('/community/messages/{msgId}/like',        [CommunityController::class, 'likeMessage']);

    // Jobs
    Route::get('/jobs',  [JobsController::class, 'index']);
    Route::post('/jobs', [JobsController::class, 'store']);

    // Quizzes
    Route::get('/quizzes',              [QuizzesController::class, 'index']);
    Route::get('/quizzes/my-results',   [QuizzesController::class, 'myResults']);
    Route::get('/quizzes/{id}',         [QuizzesController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizzesController::class, 'submit']);

    // Availability
    Route::prefix('mentor/availability')->group(function () {
        Route::get   ('/',              [AvailabilityController::class, 'index']);       // GET    all own slots
        Route::get   ('/available',     [AvailabilityController::class, 'available']);   // GET    only active slots
        Route::post  ('/',              [AvailabilityController::class, 'store']);       // POST   add single slot
        Route::put   ('/',              [AvailabilityController::class, 'update']);      // PUT    bulk replace all slots
        Route::put   ('/{id}',          [AvailabilityController::class, 'updateSlot']); // PUT    update single slot
        Route::patch ('/{id}/toggle',   [AvailabilityController::class, 'toggle']);     // PATCH  toggle single slot
        Route::delete('/{id}',          [AvailabilityController::class, 'destroy']);    // DELETE single slot
        Route::delete('/',              [AvailabilityController::class, 'destroyAll']); // DELETE all slots

        
    });

    Route::get(
        'mentors/{mentorId}/availability',
        [AvailabilityController::class, 'getByMentor']
    );

    

    // Reviews, Calls, Wellness, Referrals, Progress, Plans, Assignments
    Route::post('/reviews',                      [ReviewsController::class, 'store']);
    Route::get('/calls',                         [CallsController::class, 'index']);
    Route::post('/calls/start',                  [CallsController::class, 'start']);
    Route::patch('/calls/{id}/end',              [CallsController::class, 'end']);
    Route::post('/wellness/mood',                [WellnessController::class, 'logMood']);
    Route::get('/wellness/history',              [WellnessController::class, 'history']);
    Route::get('/referrals/my-code',             [ReferralsController::class, 'myCode']);
    Route::get('/progress',                      [ProgressController::class, 'index']);
    Route::get('/plans',                         [PlansController::class, 'index']);
    Route::post('/plans/subscribe',              [PlansController::class, 'subscribe']);
    Route::get('/assignments/my-mentees',        [AssignmentsController::class, 'myMentees']);
    Route::get('/assignments/my-mentor',         [AssignmentsController::class, 'myMentor']);
    Route::post('/assignments/assign',           [AssignmentsController::class, 'assign']);

    // Admin
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard',                   [AdminController::class, 'dashboard']);
        Route::get('/users',                       [AdminController::class, 'users']);
        Route::get('/users/{id}',                  [AdminController::class, 'showUser']);
        Route::patch('/users/{id}',                [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}',               [AdminController::class, 'deleteUser']);
        Route::get('/sessions',                    [AdminController::class, 'sessions']);
        Route::get('/jobs',                        [AdminController::class, 'jobs']);
        Route::post('/jobs',                       [AdminController::class, 'createJob']);
        Route::patch('/jobs/{id}',                 [AdminController::class, 'updateJob']);
        Route::delete('/jobs/{id}',                [AdminController::class, 'deleteJob']);
        Route::get('/videos',                      [AdminController::class, 'videos']);
        Route::delete('/videos/{id}',              [AdminController::class, 'deleteVideo']);
        Route::get('/quizzes',                     [AdminController::class, 'quizzes']);
        Route::post('/quizzes',                    [AdminController::class, 'createQuiz']);
        Route::post('/quizzes/{id}/questions',     [AdminController::class, 'addQuestion']);
        Route::get('/plans',                       [AdminController::class, 'plans']);
        Route::post('/plans',                      [AdminController::class, 'createPlan']);
        Route::get('/assessments',                 [AdminController::class, 'assessments']);
        Route::post('/assessments',                [AdminController::class, 'createAssessment']);
        Route::get('/channels',                    [AdminController::class, 'communityChannels']);
        Route::post('/channels',                   [AdminController::class, 'createChannel']);
        Route::post('/notifications/broadcast',    [AdminController::class, 'broadcastNotification']);
    });

    // ── Mentee Onboarding ─────────────────────────────────────────
    // Base: /api/v1/mentee/onboarding
    Route::prefix('v1/mentee/onboarding')->name('mentee.onboarding.')->group(function () {
 
        Route::get ('meta',     [MenteeOnboarding::class, 'meta']);      // GET  meta & streams
        Route::get ('status',   [MenteeOnboarding::class, 'status']);    // GET  current progress
 
        Route::post('step/1',   [MenteeOnboarding::class, 'saveStep1']); // POST basic info
        Route::post('step/2',   [MenteeOnboarding::class, 'saveStep2']); // POST education
        Route::post('step/3',   [MenteeOnboarding::class, 'saveStep3']); // POST career goals
        Route::post('complete', [MenteeOnboarding::class, 'complete']);   // POST mark complete
    });
 
    // ── Mentor Onboarding ─────────────────────────────────────────
    // Base: /api/v1/mentor/onboarding
    Route::prefix('v1/mentor/onboarding')->name('mentor.onboarding.')->group(function () {
 
        Route::get ('meta',     [MentorOnboarding::class, 'meta']);      // GET  meta & streams
        Route::get ('status',   [MentorOnboarding::class, 'status']);    // GET  current progress
 
        Route::post('step/1',   [MentorOnboarding::class, 'saveStep1']); // POST basic info + avatar
        Route::post('step/2',   [MentorOnboarding::class, 'saveStep2']); // POST professional details
        Route::post('step/3',   [MentorOnboarding::class, 'saveStep3']); // POST expertise chips
        Route::post('step/4',   [MentorOnboarding::class, 'saveStep4']); // POST preferences & strengths
        Route::post('submit',   [MentorOnboarding::class, 'submit']);     // POST submit for approval
    });
});
