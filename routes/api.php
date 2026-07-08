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
use App\Http\Controllers\Api\AssignmentsController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Mentee\OnboardingController as MenteeOnboarding;
use App\Http\Controllers\Api\Mentee\CurriculumController as MenteeCurriculum;
use App\Http\Controllers\Api\Mentee\MentorRequestController as MenteeMentorRequest;
use App\Http\Controllers\Api\Mentee\ProgressController as MenteeProgress;
use App\Http\Controllers\Api\Mentor\OnboardingController as MentorOnboarding;
use App\Http\Controllers\Api\Mentor\CurriculumController as MentorCurriculum;
use App\Http\Controllers\Api\Mentor\MenteeController as MentorMentee;
use App\Http\Controllers\Api\Mentor\MentorRequestController as MentorMentorRequest;

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
    Route::get('/media/mentor-videos/{filename}', [VideosController::class, 'serveMentorVideoFile'])
        ->where('filename', '[^/]+');
    Route::get('/media/curriculum-supporting-materials/{filename}', [VideosController::class, 'serveSupportingMaterialFile'])
        ->where('filename', '[^/]+');
    Route::get('/media/curriculum-tasks/{filename}', [VideosController::class, 'serveCurriculumTaskFile'])
        ->where('filename', '[^/]+');
});

// Public Auth
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);
    Route::post('/send-otp',    [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp',  [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp',  [AuthController::class, 'resendOtp']);
});

Route::prefix('v1')->group(function () {
    Route::post('/get-agora-token/{channel}',           [SessionsController::class, 'getAgoraToken']);
});

// Protected
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    /**********************************************************
     * Auth & Onboarding
     **********************************************************/
     
    Route::prefix('auth')->group(function () {
        Route::get('/me',               [AuthController::class, 'me']);
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::patch('/profile',        [AuthController::class, 'updateProfile']);
        Route::post('/upload-photo',    [AuthController::class, 'uploadPhoto']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::patch('/onboarding',     [AuthController::class, 'updateOnboarding']);
    });

    /**********************************************************
     * Admin Routes
     **********************************************************/

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

    /**********************************************************
     * Mentee Routes
     **********************************************************/
    Route::middleware('mentee')->prefix('mentee')->name('mentee.')->group(function () {
        Route::delete('account', [MenteeOnboarding::class, 'destroyAccount'])->name('account.destroy');

        // ── Mentee Onboarding ─────────────────────────────────────────
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
    
            Route::get ('meta',     [MenteeOnboarding::class, 'meta']);      // GET  meta & streams
            Route::get ('status',   [MenteeOnboarding::class, 'status']);    // GET  current progress
    
            Route::post('step/1',   [MenteeOnboarding::class, 'saveStep1']); // POST profile
            Route::post('step/2',   [MenteeOnboarding::class, 'saveStep2']); // POST education details
            Route::post('step/3',   [MenteeOnboarding::class, 'saveStep3']); // POST career goals
            Route::post('step/4',   [MenteeOnboarding::class, 'saveStep4']); // POST preferences
            Route::post('complete', [MenteeOnboarding::class, 'complete']);   // POST mark complete
        });

        // Curriculum (mentor-assigned) + admin MCQs
        Route::prefix('curriculum')->name('curriculum.')->group(function () {
            Route::get('/',           [MenteeCurriculum::class, 'index'])->name('index');
            Route::get('/tasks',      [MenteeCurriculum::class, 'tasks'])->name('tasks');
            Route::get('/mcqs',       [MenteeCurriculum::class, 'mcqs'])->name('mcqs');
            Route::get('/admin-mcqs', [MenteeCurriculum::class, 'adminMcqs'])->name('adminMcqs');
            Route::post('/tasks/{task}/complete', [MenteeProgress::class, 'completeTask'])->name('tasks.complete')->whereNumber('task');
            Route::post('/mcqs/{mcq}/answer',     [MenteeProgress::class, 'answerMcq'])->name('mcqs.answer')->whereNumber('mcq');
        });

        // Progress dashboard
        Route::get('progress', [MenteeProgress::class, 'index'])->name('progress');

        // Mentor video collections (all active)
        Route::get('mentor-videos', [VideosController::class, 'menteeMentorVideos'])->name('mentor-videos');
        Route::post('mentor-videos/files/{file}/watched', [MenteeProgress::class, 'markVideoWatched'])->name('mentor-videos.watched')->whereNumber('file');

        // Mentor selection requests
        Route::prefix('mentor-requests')->name('mentor-requests.')->group(function () {
            Route::get ('/',      [MenteeMentorRequest::class, 'index'])->name('index');
            Route::post('/',      [MenteeMentorRequest::class, 'store'])->name('store');
            Route::delete('/{id}', [MenteeMentorRequest::class, 'destroy'])->name('destroy')->whereNumber('id');
        });

        // Mentors
        Route::prefix('mentors')->name('mentors.')->group(function () {
            Route::get('/',                  [MentorsController::class, 'index']);
            Route::get('/{id}',              [MentorsController::class, 'show']);
            Route::get('/{id}/availability', [MentorsController::class, 'availability']);
        });

        // Sessions
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/',          [SessionsController::class, 'index']);
            Route::post('/',         [SessionsController::class, 'store']);
            Route::patch('/{id}',    [SessionsController::class, 'update']);
            Route::delete('/{id}',   [SessionsController::class, 'destroy']);
        });

        // Assessments
        Route::prefix('assessments')->name('assessments.')->group(function () {
            Route::get('/',                 [AssessmentsController::class, 'index'])->name('index');
            Route::get('/{id}',             [AssessmentsController::class, 'show'])->name('show');
            Route::post('/{id}/submit',     [AssessmentsController::class, 'submit'])->name('submit');
        });


        // Wallet
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/balance',         [WalletController::class, 'balance'])->name('balance');
            Route::get('/transactions',    [WalletController::class, 'transactions'])->name('transactions');
            Route::post('/topup',          [WalletController::class, 'topup'])->name('topup');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                    [NotificationsController::class, 'index'])->name('index');
            Route::patch('/{id}/read',         [NotificationsController::class, 'markRead'])->name('read');
            Route::post('/mark-all-read',      [NotificationsController::class, 'markAllRead'])->name('markAllRead');
        });

        // Career tracks
        Route::prefix('career-tracks')->name('career-tracks.')->group(function () {
            Route::get('/',                                   [CareerTracksController::class, 'index'])->name('index');
            Route::post('/',                                  [CareerTracksController::class, 'store'])->name('store');
            Route::patch('/milestones/{id}/complete',         [CareerTracksController::class, 'updateMilestone'])->name('updateMilestone');
        });

        // Community
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/channels',                       [CommunityController::class, 'channels'])->name('channels');
            Route::get('/channels/{channelId}/messages',  [CommunityController::class, 'messages'])->name('messages');
            Route::post('/channels/{channelId}/messages', [CommunityController::class, 'postMessage'])->name('postMessage');
            Route::post('/messages/{msgId}/like',         [CommunityController::class, 'likeMessage'])->name('likeMessage');
        });

        // Jobs
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/',      [JobsController::class, 'index'])->name('index');
            Route::post('/',     [JobsController::class, 'store'])->name('store');
            Route::post('apply-job', [JobsController::class, 'applyJob']);
        });

        // Quizzes
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/',                  [QuizzesController::class, 'index'])->name('index');
            Route::get('/my-results',        [QuizzesController::class, 'myResults'])->name('myResults');
            Route::get('/{id}',              [QuizzesController::class, 'show'])->name('show');
            Route::post('/{id}/submit',      [QuizzesController::class, 'submit'])->name('submit');
        });

        // Availability
        Route::get('mentors/{mentorId}/availability',[AvailabilityController::class, 'getByMentor']);

        //Plans
        Route::prefix('plans')->group(function () {
            Route::post('/subscribe/{id}', [PlanController::class, 'subscribe']);                    
            Route::get('/subscription/active', [PlanController::class, 'activeSubscription']);  
            Route::get('/subscription/history', [PlanController::class, 'subscriptionHistory']);
            Route::post('/subscription/cancel', [PlanController::class, 'cancelSubscription']); 
        });

    });

    /**********************************************************
     * Mentor Routes
     **********************************************************/
    // Base: /api/v1/mentor/onboarding
    Route::middleware('mentor')->prefix('mentor')->name('mentor.')->group(function () {
        Route::delete('account', [MentorOnboarding::class, 'destroyAccount'])->name('account.destroy');

        // ── Mentor Onboarding ─────────────────────────────────────────
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
    
            Route::get ('meta',     [MentorOnboarding::class, 'meta']);      // GET  meta & streams
            Route::get ('status',   [MentorOnboarding::class, 'status']);    // GET  current progress
    
            Route::post('step/1',   [MentorOnboarding::class, 'saveStep1']); // POST basic info + avatar
            Route::post('step/2',   [MentorOnboarding::class, 'saveStep2']); // POST professional details
            Route::post('step/3',   [MentorOnboarding::class, 'saveStep3']); // POST expertise chips
            Route::post('step/4',   [MentorOnboarding::class, 'saveStep4']); // POST preferences & strengths
            Route::post('submit',   [MentorOnboarding::class, 'submit']);     // POST submit for approval
        });

        // ── Mentor Curriculum (Track → Month → Week → Task) ─────────────
        Route::prefix('curriculum')->name('curriculum.')->group(function () {
            Route::get ('tracks',                    [MentorCurriculum::class, 'tracks']);
            Route::post('tracks',                     [MentorCurriculum::class, 'storeTrack']);
            Route::post('tracks/{track}/months',      [MentorCurriculum::class, 'storeMonth'])->whereNumber('track');
            Route::get ('tracks/{track}/months',      [MentorCurriculum::class, 'months'])->whereNumber('track');
            Route::patch('months/{month}',            [MentorCurriculum::class, 'updateMonth'])->whereNumber('month');
            Route::delete('months/{month}',           [MentorCurriculum::class, 'destroyMonth'])->whereNumber('month');
            Route::post('months/{month}/weeks',       [MentorCurriculum::class, 'storeWeek'])->whereNumber('month');
            Route::get ('months/{month}/weeks',       [MentorCurriculum::class, 'weeks'])->whereNumber('month');
            Route::patch('weeks/{week}',              [MentorCurriculum::class, 'updateWeek'])->whereNumber('week');
            Route::delete('weeks/{week}',            [MentorCurriculum::class, 'destroyWeek'])->whereNumber('week');
            Route::post('weeks/{week}/tasks',        [MentorCurriculum::class, 'storeTask'])->whereNumber('week');
            Route::get ('weeks/{week}/tasks',        [MentorCurriculum::class, 'tasks'])->whereNumber('week');
            Route::get   ('weeks/{week}/mcqs',        [MentorCurriculum::class, 'mcqs'])->whereNumber('week');
            Route::post  ('weeks/{week}/mcqs',        [MentorCurriculum::class, 'storeMcq'])->whereNumber('week');
            Route::patch ('weeks/{week}/mcqs/{topic}', [MentorCurriculum::class, 'updateMcq'])->whereNumber('week')->whereNumber('topic');
            Route::delete('weeks/{week}/mcqs/{topic}', [MentorCurriculum::class, 'destroyMcq'])->whereNumber('week')->whereNumber('topic');
            Route::post('tasks/{task}',             [MentorCurriculum::class, 'updateTask'])->whereNumber('task');
            Route::patch('tasks/{task}',             [MentorCurriculum::class, 'updateTask'])->whereNumber('task');
            Route::delete('tasks/{task}',            [MentorCurriculum::class, 'destroyTask'])->whereNumber('task');
            Route::get ('weeks/{week}/supporting-materials', [MentorCurriculum::class, 'supportingMaterials'])->whereNumber('week');
            Route::post('weeks/{week}/supporting-materials', [MentorCurriculum::class, 'storeSupportingMaterial'])->whereNumber('week');
            Route::post('supporting-materials/{material}', [MentorCurriculum::class, 'updateSupportingMaterial'])->whereNumber('material');
            Route::patch('supporting-materials/{material}', [MentorCurriculum::class, 'updateSupportingMaterial'])->whereNumber('material');
            Route::delete('supporting-materials/{material}', [MentorCurriculum::class, 'destroySupportingMaterial'])->whereNumber('material');
        });

        // ── Mentor Mentees ────────────────────────────────────────────
        Route::prefix('mentees')->name('mentees.')->group(function () {
            Route::get('/',       [MentorMentee::class, 'index'])->name('index');
            Route::get('/{mentee}', [MentorMentee::class, 'show'])->name('show');
        });

        // ── Mentor Requests (accept / reject) ─────────────────────────
        Route::prefix('mentor-requests')->name('mentor-requests.')->group(function () {
            Route::get ('/',              [MentorMentorRequest::class, 'index'])->name('index');
            Route::post('/{id}/accept',   [MentorMentorRequest::class, 'accept'])->name('accept')->whereNumber('id');
            Route::post('/{id}/reject',   [MentorMentorRequest::class, 'reject'])->name('reject')->whereNumber('id');
        });

        Route::prefix('availability')->name('availability.')->group(function () {
            Route::get   ('/',              [AvailabilityController::class, 'index']);       // GET    all own slots
            Route::get   ('/available',     [AvailabilityController::class, 'available']);   // GET    only active slots
            Route::post  ('/',              [AvailabilityController::class, 'store']);       // POST   add single slot
            Route::put   ('/',              [AvailabilityController::class, 'update']);      // PUT    bulk replace all slots
            Route::put   ('/{id}',          [AvailabilityController::class, 'updateSlot']); // PUT    update single slot
            Route::patch ('/{id}/toggle',   [AvailabilityController::class, 'toggle']);     // PATCH  toggle single slot
            Route::delete('/{id}',          [AvailabilityController::class, 'destroy']);    // DELETE single slot
            Route::delete('/',              [AvailabilityController::class, 'destroyAll']); // DELETE all slots
        });

        // Mentors
        Route::prefix('mentors')->name('mentors.')->group(function () {
            Route::get('/',                [MentorsController::class, 'index'])->name('index');
            Route::get('/{id}',            [MentorsController::class, 'show'])->name('show');
            Route::get('/{id}/availability', [MentorsController::class, 'availability'])->name('availability');
        });

        // Sessions
        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/',            [SessionsController::class, 'index'])->name('index');
            Route::post('/',           [SessionsController::class, 'store'])->name('store');
            Route::patch('/{id}',      [SessionsController::class, 'update'])->name('update');
            Route::delete('/{id}',     [SessionsController::class, 'destroy'])->name('destroy');
        });

        // Assessments
        Route::prefix('assessments')->name('assessments.')->group(function () {
            Route::get('/',                     [AssessmentsController::class, 'index'])->name('index');
            Route::get('/{id}',                [AssessmentsController::class, 'show'])->name('show');
            Route::post('/{id}/submit',        [AssessmentsController::class, 'submit'])->name('submit');
        });

        // Videos
        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get   ('/',              [VideosController::class, 'mentorIndex'])->name('index');
            Route::post  ('/',              [VideosController::class, 'mentorStore'])->name('store');
            Route::post  ('/{id}/watched',  [VideosController::class, 'markWatched'])->name('watched')->whereNumber('id');
            Route::post  ('/{id}',          [VideosController::class, 'mentorUpdate'])->name('update')->whereNumber('id');
            Route::delete('/{id}',          [VideosController::class, 'mentorDestroy'])->name('destroy')->whereNumber('id');
        });

        // Wallet
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/balance',           [WalletController::class, 'balance'])->name('balance');
            Route::get('/transactions',      [WalletController::class, 'transactions'])->name('transactions');
            Route::post('/topup',            [WalletController::class, 'topup'])->name('topup');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                        [NotificationsController::class, 'index'])->name('index');
            Route::patch('/{id}/read',             [NotificationsController::class, 'markRead'])->name('markRead');
            Route::post('/mark-all-read',          [NotificationsController::class, 'markAllRead'])->name('markAllRead');
        });

        // Career Tracks
        Route::prefix('career-tracks')->name('career-tracks.')->group(function () {
            Route::get('/',                                    [CareerTracksController::class, 'index'])->name('index');
            Route::post('/',                                   [CareerTracksController::class, 'store'])->name('store');
            Route::patch('/milestones/{id}/complete',          [CareerTracksController::class, 'updateMilestone'])->name('milestone.complete');
        });

        // Community
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/channels',                             [CommunityController::class, 'channels'])->name('channels');
            Route::get('/channels/{channelId}/messages',         [CommunityController::class, 'messages'])->name('channels.messages');
            Route::post('/channels/{channelId}/messages',        [CommunityController::class, 'postMessage'])->name('channels.messages.post');
            Route::post('/messages/{msgId}/like',                [CommunityController::class, 'likeMessage'])->name('messages.like');
        });

        // Jobs
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/',      [JobsController::class, 'index'])->name('index');
            Route::post('/',     [JobsController::class, 'store'])->name('store');
        });

        // Quizzes
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/',                 [QuizzesController::class, 'index'])->name('index');
            Route::get('/my-results',       [QuizzesController::class, 'myResults'])->name('myResults');
            Route::get('/{id}',             [QuizzesController::class, 'show'])->name('show');
            Route::post('/{id}/submit',     [QuizzesController::class, 'submit'])->name('submit');
        });


    });

    // Reviews, Calls, Wellness, Referrals, Progress, Plans, Assignments
    Route::post('/reviews',                      [ReviewsController::class, 'store']);
    Route::get('/calls',                         [CallsController::class, 'index']);
    Route::post('/calls/start',                  [CallsController::class, 'start']);
    Route::patch('/calls/{id}/end',              [CallsController::class, 'end']);
    Route::post('/wellness/mood',                [WellnessController::class, 'logMood']);
    Route::get('/wellness/history',              [WellnessController::class, 'history']);
    Route::get('/referrals/my-code',             [ReferralsController::class, 'myCode']);
    Route::get('/assignments/my-mentees',        [AssignmentsController::class, 'myMentees']);
    Route::get('/assignments/my-mentor',         [AssignmentsController::class, 'myMentor']);
    Route::post('/assignments/assign',           [AssignmentsController::class, 'assign']);

    Route::prefix('plans')->group(function () {
        Route::get('/', [PlanController::class, 'index']);        
        Route::get('/{id}', [PlanController::class, 'show']);  
    });
});
