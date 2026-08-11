<?php

/*
|=============================================================
|  Vedrix  —  routes/web.php  (FRONTEND)
|  Covers: Public pages, Auth (email+OTP), Mentor onboarding,
|  Mentee onboarding, Mentor dashboard, Mentee dashboard,
|  Sessions, Wallet, Journey, Community, Jobs, Wellness, Quiz
|=============================================================
*/

use Illuminate\Support\Facades\Route;

// ── Controllers ─────────────────────────────────────────────
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\MentorListingController;
use App\Http\Controllers\Frontend\ContactController;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ForgotPasswordController;

use App\Http\Controllers\Mentor\OnboardingController   as MentorOnboardingController;
use App\Http\Controllers\Mentor\DashboardController    as MentorDashboardController;
use App\Http\Controllers\Mentor\SessionController      as MentorSessionController;
use App\Http\Controllers\Mentor\WalletController       as MentorWalletController;
use App\Http\Controllers\Mentor\ProfileController      as MentorProfileController;
use App\Http\Controllers\Mentor\AvailabilityController as MentorAvailabilityController;
use App\Http\Controllers\Mentor\PortalController       as MentorPortalController;
use App\Http\Controllers\Mentor\CurriculumController  as MentorCurriculumController;

use App\Http\Controllers\Mentee\OnboardingController   as MenteeOnboardingController;
use App\Http\Controllers\Mentee\DashboardController    as MenteeDashboardController;
use App\Http\Controllers\Mentee\SessionController      as MenteeSessionController;
use App\Http\Controllers\Mentee\WalletController       as MenteeWalletController;
use App\Http\Controllers\Mentee\PlanController         as MenteePlanController;
use App\Http\Controllers\Mentee\InvoiceController     as MenteeInvoiceController;
use App\Http\Controllers\Mentee\SessionInvoiceController as MenteeSessionInvoiceController;
use App\Http\Controllers\Mentee\BookingController;
use App\Http\Controllers\Mentee\JourneyController;
use App\Http\Controllers\Mentee\AssessmentController as MenteeAssessmentController;
use App\Http\Controllers\Mentee\QuizController as MenteeQuizController;
use App\Http\Controllers\Mentee\CommunityController as MenteeCommunityController;
use App\Http\Controllers\Mentee\JobController as MenteeJobController;
use App\Http\Controllers\Mentee\MentorRequestController as MenteeMentorRequestController;
use App\Http\Controllers\Mentor\MentorRequestController as MentorMentorRequestController;

// ── Admin controllers (already exist in your backend) ───────
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\WalletTransactionController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Controllers\Admin\VideoCallLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\SessionInvoiceController as AdminSessionInvoiceController;
use App\Http\Controllers\Admin\JobListingController;
use App\Http\Controllers\Admin\ConsultationSessionController;
use App\Http\Controllers\Admin\SessionReviewController;
use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\WellnessSurveyController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\JourneyController as AdminJourneyController;
use App\Http\Controllers\Admin\AdminOnboardingController;
use App\Http\Controllers\Admin\MentorApprovalController;
use App\Http\Controllers\Admin\MentorProfileController as AdminMentorProfileController;
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\NotificationController;

use App\Services\PublicFileStorage;
use Illuminate\Support\Facades\Artisan;

Route::get('/storage-link', function () {
    PublicFileStorage::ensureStorageReady(force: true);

    $link = public_path('storage');
    $writable = is_writable(storage_path('app/public'));
    $linked = file_exists($link) || is_link($link);

    return response()->json([
        'message' => 'Storage setup complete.',
        'storage_app_public_writable' => $writable,
        'public_storage_link_exists' => $linked,
        'serve_via' => $linked ? 'symlink' : 'laravel route /storage/{path}',
    ]);
});

/*
|=============================================================
|  PUBLIC ROUTES  (no auth required)
|=============================================================
*/

// ── Home ────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/waitlist', [HomeController::class, 'waitlist'])->name('waitlist.store');

// ── Mentor Listing & Profile ────────────────────────────────
Route::get('/mentors',      [MentorListingController::class, 'index'])->name('mentors.search');
Route::get('/mentors/{slug}', [MentorListingController::class, 'show'])->name('mentors.show');

// Mentor availability (called by booking widget via AJAX)
Route::get('/api/mentors/{id}/availability', [MentorListingController::class, 'availability'])
     ->name('mentors.availability');

// ── Static / Info Pages ─────────────────────────────────────
Route::get('/about',   fn() => view('frontend.about'))  ->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact',[ContactController::class, 'send'])->name('contact.send');
Route::get('/privacy', fn() => view('frontend.privacy'))->name('privacy');
Route::get('/terms',   fn() => view('frontend.terms'))  ->name('terms');

// Public job listings
Route::get('/jobs',      [JobListingController::class, 'publicIndex'])->name('jobs.public');
Route::get('/jobs/{id}', [JobListingController::class, 'publicShow']) ->name('jobs.public.show');

/*
|=============================================================
|  AUTHENTICATION  (guest only)
|=============================================================
*/
Route::middleware('guest')->group(function () {

    // ── Register ─────────────────────────────────────────────
    Route::get( '/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']) ->name('register.post');

    // ── Login ────────────────────────────────────────────────
    Route::get( '/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])   ->name('login.post');

    // ── OTP (registration + login via phone) ─────────────────
    Route::post('/auth/send-otp',       [OtpController::class, 'send'])       ->name('otp.send');
    Route::post('/auth/verify-otp',     [OtpController::class, 'verify'])     ->name('otp.verify');
    Route::post('/auth/send-login-otp', [OtpController::class, 'sendLogin'])  ->name('otp.send-login');
    Route::post('/auth/login-otp',      [OtpController::class, 'loginWithOtp'])->name('otp.login');

    // ── Password reset ────────────────────────────────────────
    Route::get( '/forgot-password',         [ForgotPasswordController::class, 'showForm'])   ->name('password.request');
    Route::post('/forgot-password',         [ForgotPasswordController::class, 'sendLink'])   ->name('password.email');
    Route::get( '/reset-password/{token}',  [ForgotPasswordController::class, 'showReset'])  ->name('password.reset');
    Route::post('/reset-password',          [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// ── Logout (auth required) ───────────────────────────────────
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|=============================================================
|  MENTOR ONBOARDING  (auth + role:mentor + not yet approved)
|=============================================================
*/
Route::middleware(['auth', 'role:mentor'])->group(function () {

    // Redirect bare URL to step 1
    Route::get('/mentor/onboarding', fn() => redirect()->route('mentor.onboarding', ['step' => 1]));

    Route::prefix('mentor/onboarding')->name('mentor.onboarding')->group(function () {
        Route::get('/{step}',   [MentorOnboardingController::class, 'show'])
             ->where('step', '[1-5]')
             ->name('');                        // route name: mentor.onboarding  (+ ?step=N)

        Route::post('/step/1',  [MentorOnboardingController::class, 'saveStep1'])->name('.save1');
        Route::post('/step/2',  [MentorOnboardingController::class, 'saveStep2'])->name('.save2');
        Route::post('/step/3',  [MentorOnboardingController::class, 'saveStep3'])->name('.save3');
        Route::post('/step/4',  [MentorOnboardingController::class, 'saveStep4'])->name('.save4');
        Route::post('/submit',  [MentorOnboardingController::class, 'submit'])   ->name('.submit');
        Route::get('/pending',  fn() => view('onboarding.mentor.pending'))        ->name('.pending');
    });
});

/*
|=============================================================
|  MENTEE ONBOARDING  (auth + role:mentee + not completed)
|=============================================================
*/
Route::middleware(['auth', 'role:mentee'])->group(function () {

    Route::get('/mentee/onboarding', fn() => redirect()->route('mentee.onboarding', ['step' => 1]));

    Route::prefix('mentee/onboarding')->name('mentee.onboarding')->group(function () {
        Route::get('/{step}',   [MenteeOnboardingController::class, 'show'])
             ->where('step', '[1-4]')
             ->name('');

        Route::post('/step/1',  [MenteeOnboardingController::class, 'saveStep1'])->name('.save1');
        Route::post('/step/2',  [MenteeOnboardingController::class, 'saveStep2'])->name('.save2');
        Route::post('/step/3',  [MenteeOnboardingController::class, 'saveStep3'])->name('.save3');
        Route::post('/complete',[MenteeOnboardingController::class, 'complete'])  ->name('.complete');
    });
});

/*
|=============================================================
|  MENTOR DASHBOARD  (auth + role:mentor + approved)
|=============================================================
*/
Route::middleware(['auth', 'role:mentor', 'mentor.approved'])
     ->prefix('mentor')
     ->name('mentor.')
     ->group(function () {

    // Dashboard
    Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');

    // Mentee assignment requests
    Route::get( '/requests',              [MentorMentorRequestController::class, 'index'])->name('requests');
    Route::post('/requests/{id}/accept',  [MentorMentorRequestController::class, 'accept'])->name('requests.accept')->whereNumber('id');
    Route::post('/requests/{id}/reject',  [MentorMentorRequestController::class, 'reject'])->name('requests.reject')->whereNumber('id');

    // Sessions
    Route::get( '/sessions',              [MentorSessionController::class, 'index'])   ->name('sessions');
    Route::get( '/sessions/{id}',         [MentorSessionController::class, 'show'])    ->name('sessions.show');
    Route::post('/sessions/{id}/confirm', [MentorSessionController::class, 'confirm']) ->name('sessions.confirm');
    Route::post('/sessions/{id}/cancel',  [MentorSessionController::class, 'cancel'])  ->name('sessions.cancel');
    Route::post('/sessions/{id}/complete',[MentorSessionController::class, 'complete'])->name('sessions.complete');
    Route::post('/sessions/{id}/no-show', [MentorSessionController::class, 'noShow'])  ->name('sessions.noshow');
    Route::post('/sessions/{id}/notes',   [MentorSessionController::class, 'addNote']) ->name('sessions.notes');
    Route::match(['patch', 'post'], '/sessions/{id}/meeting-link', [MentorSessionController::class, 'updateMeetingLink'])
        ->name('sessions.meeting-link');

    // Availability
    Route::get( '/availability',       [MentorAvailabilityController::class, 'show'])  ->name('availability');
    Route::post('/availability',       [MentorAvailabilityController::class, 'update'])->name('availability.update');
    Route::post('/availability/settings', [MentorAvailabilityController::class, 'updateSettings'])->name('availability.settings');
    Route::post('/availability/toggle-live', [MentorAvailabilityController::class, 'toggleLive'])->name('availability.toggle-live');
    Route::post('/availability/block', [MentorAvailabilityController::class, 'blockDate'])->name('availability.block');
    Route::delete('/availability/block/{date}', [MentorAvailabilityController::class, 'unblockDate'])->name('availability.unblock');

    // Wallet / Earnings
    Route::get('/wallet',              [MentorWalletController::class, 'index'])        ->name('wallet');
    Route::post('/wallet/withdraw',    [MentorWalletController::class, 'withdraw'])     ->name('wallet.withdraw');

    // Profile edit
    Route::get( '/profile/edit',  [MentorProfileController::class, 'edit'])   ->name('profile.edit');
    Route::put( '/profile',       [MentorProfileController::class, 'update']) ->name('profile.update');
    Route::post('/profile/avatar',[MentorProfileController::class, 'avatar']) ->name('profile.avatar');

    // Session notes / mentees / journey / community / assessments
    Route::get('/notes',                          [MentorPortalController::class, 'notes'])->name('notes');
    Route::get('/mentees',                        [MentorPortalController::class, 'mentees'])->name('mentees');
    Route::get('/mentees/{id}',                   [MentorPortalController::class, 'menteeShow'])->name('mentees.show');
    Route::get('/journey',                        [MentorPortalController::class, 'journey'])->name('journey');
    Route::get('/journey/{mentee}',               [MentorPortalController::class, 'journeyShow'])->name('journey.show');
    Route::get('/community',                      [MentorPortalController::class, 'community'])->name('community');
    Route::get('/community/{channel:slug}',       [MentorPortalController::class, 'communityShow'])->name('community.show');
    Route::post('/community/{channel:slug}/join', [MentorPortalController::class, 'communityJoin'])->name('community.join');
    Route::post('/community/{channel:slug}/messages', [MessageController::class, 'store'])->name('community.messages.store');
    Route::post('/community/messages/{message}/like', [MessageController::class, 'like'])->name('community.messages.like');
    Route::delete('/community/messages/{message}', [MessageController::class, 'destroy'])->name('community.messages.destroy');
    Route::get('/assessments',                    [MentorPortalController::class, 'assessments'])->name('assessments');

    // Curriculum builder (mirrors /api/v1/mentor/curriculum/*)
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        Route::get('/',                                 [MentorCurriculumController::class, 'tracks'])->name('tracks');
        Route::post('/tracks',                          [MentorCurriculumController::class, 'storeTrack'])->name('tracks.store');
        Route::put('/tracks/{track}',                   [MentorCurriculumController::class, 'updateTrack'])->name('tracks.update');

        Route::get('/tracks/{track}/months',            [MentorCurriculumController::class, 'months'])->name('months');
        Route::post('/tracks/{track}/months',           [MentorCurriculumController::class, 'storeMonth'])->name('months.store');
        Route::put('/months/{month}',                   [MentorCurriculumController::class, 'updateMonth'])->name('months.update');
        Route::delete('/months/{month}',                [MentorCurriculumController::class, 'destroyMonth'])->name('months.destroy');

        Route::get('/months/{month}/weeks',             [MentorCurriculumController::class, 'weeks'])->name('weeks');
        Route::post('/months/{month}/weeks',            [MentorCurriculumController::class, 'storeWeek'])->name('weeks.store');
        Route::put('/weeks/{week}',                     [MentorCurriculumController::class, 'updateWeek'])->name('weeks.update');
        Route::delete('/weeks/{week}',                  [MentorCurriculumController::class, 'destroyWeek'])->name('weeks.destroy');

        Route::post('/weeks/{week}/tasks',              [MentorCurriculumController::class, 'storeTask'])->name('tasks.store');
        Route::match(['put', 'post'], '/tasks/{task}',  [MentorCurriculumController::class, 'updateTask'])->name('tasks.update');
        Route::delete('/tasks/{task}',                  [MentorCurriculumController::class, 'destroyTask'])->name('tasks.destroy');

        Route::post('/weeks/{week}/mcqs',               [MentorCurriculumController::class, 'storeMcqTopic'])->name('mcqs.store');
        Route::put('/weeks/{week}/mcqs/{topic}',        [MentorCurriculumController::class, 'updateMcqTopic'])->name('mcqs.update');
        Route::delete('/weeks/{week}/mcqs/{topic}',     [MentorCurriculumController::class, 'destroyMcqTopic'])->name('mcqs.destroy');
        Route::delete('/weeks/{week}/mcqs/{topic}/{mcq}', [MentorCurriculumController::class, 'destroyMcqItem'])->name('mcqs.item.destroy');

        Route::post('/weeks/{week}/supporting-materials', [MentorCurriculumController::class, 'storeSupportingMaterial'])->name('materials.store');
        Route::match(['put', 'post'], '/supporting-materials/{material}', [MentorCurriculumController::class, 'updateSupportingMaterial'])->name('materials.update');
        Route::delete('/supporting-materials/{material}', [MentorCurriculumController::class, 'destroySupportingMaterial'])->name('materials.destroy');
    });
});

/*
|=============================================================
|  MENTEE DASHBOARD  (auth + role:mentee + onboarding done)
|=============================================================
*/
Route::middleware(['auth', 'role:mentee', 'onboarding.complete'])
     ->prefix('mentee')
     ->name('mentee.')
     ->group(function () {

    // Dashboard
    Route::get('/dashboard', [MenteeDashboardController::class, 'index'])->name('dashboard');

    // Current / change mentor
    Route::get( '/mentor/change',              [MenteeMentorRequestController::class, 'change'])->name('mentor.change');
    Route::post('/mentor-requests',            [MenteeMentorRequestController::class, 'store'])->name('mentor-requests.store');
    Route::delete('/mentor-requests/{id}',     [MenteeMentorRequestController::class, 'destroy'])->name('mentor-requests.destroy')->whereNumber('id');

    // Sessions
    Route::get('/sessions',           [MenteeSessionController::class, 'index']) ->name('sessions');
    Route::get('/sessions/{id}',      [MenteeSessionController::class, 'show'])  ->name('sessions.show');
    Route::delete('/sessions/{id}',   [MenteeSessionController::class, 'cancel'])->name('sessions.cancel');

    // Book a session (called from mentor profile / search)
    Route::post('/sessions',          [BookingController::class, 'store'])       ->name('sessions.book');
    Route::post('/sessions/verify-payment', [BookingController::class, 'verifyPayment'])->name('sessions.verify');
    Route::get( '/sessions/{id}/review', [BookingController::class, 'reviewForm'])->name('sessions.review');
    Route::post('/sessions/{id}/review', [BookingController::class, 'submitReview'])->name('sessions.review.post');

    // Journey / Curriculum
    Route::get( '/journey',                        [JourneyController::class, 'index'])      ->name('journey.index');
    Route::get( '/journey/month/{month}',          [JourneyController::class, 'month'])      ->name('journey.month');
    Route::get( '/journey/week/{week}',            [JourneyController::class, 'week'])       ->name('journey.week');
    Route::post('/journey/tasks/{task}/complete',  [JourneyController::class, 'completeTask'])->name('journey.task.complete');
    Route::post('/journey/mcqs/{mcq}/answer',      [JourneyController::class, 'answerMcq']) ->name('journey.mcq.answer');
    Route::post('/journey/weeks/{week}/checkin',   [JourneyController::class, 'checkin'])   ->name('journey.checkin');

    // Wallet
    Route::get( '/wallet',                [MenteeWalletController::class, 'index'])          ->name('wallet');
    Route::post('/wallet/topup/initiate', [MenteeWalletController::class, 'initiateTopup']) ->name('wallet.topup');
    Route::post('/wallet/topup/verify',   [MenteeWalletController::class, 'verifyTopup'])   ->name('wallet.topup.verify');

    // Subscription plans (mirrors mobile app flow)
    Route::get( '/plans',                    [MenteePlanController::class, 'index'])    ->name('plans');
    Route::post('/plans/{plan}/subscribe',   [MenteePlanController::class, 'subscribe'])->name('plans.subscribe');
    Route::post('/plans/{plan}/verify',      [MenteePlanController::class, 'verify'])   ->name('plans.verify');
    Route::post('/plans/cancel',             [MenteePlanController::class, 'cancel'])   ->name('plans.cancel');
    Route::post('/subscriptions/{subscription}/invoice', [MenteeInvoiceController::class, 'generate'])->name('subscriptions.invoice');
    Route::get( '/invoices/{invoice}',          [MenteeInvoiceController::class, 'show'])     ->name('invoices.show');
    Route::get( '/invoices/{invoice}/print',    [MenteeInvoiceController::class, 'print'])    ->name('invoices.print');
    Route::get( '/invoices/{invoice}/download', [MenteeInvoiceController::class, 'download'])->name('invoices.download');
    Route::get( '/session-invoices/{invoice}/download', [MenteeSessionInvoiceController::class, 'download'])->name('session-invoices.download');

    // Quizzes
    Route::get( '/quizzes',                               [MenteeQuizController::class, 'index'])  ->name('quizzes.index');
    Route::get( '/quizzes/{quiz}',                        [MenteeQuizController::class, 'show'])   ->name('quizzes.show');
    Route::post('/quizzes/{quiz}/attempt',                [MenteeQuizController::class, 'attempt'])->name('quizzes.attempt');
    Route::post('/quizzes/{quiz}/attempt/{attempt}/submit',[MenteeQuizController::class, 'submit'])->name('quizzes.submit');
    Route::get( '/quizzes/{quiz}/attempt/{attempt}/result',[MenteeQuizController::class, 'result'])->name('quizzes.result');

    // Wellness
    Route::get( '/wellness',              [WellnessSurveyController::class, 'index'])         ->name('wellness.index');
    Route::get( '/wellness/{survey}',     [WellnessSurveyController::class, 'show'])          ->name('wellness.show');
    Route::post('/wellness/{survey}/respond', [WellnessSurveyController::class, 'respond'])   ->name('wellness.respond');

    // Assessments
    Route::get( '/assessments',           [MenteeAssessmentController::class, 'index'])  ->name('assessments.index');
    Route::get( '/assessments/{id}',      [MenteeAssessmentController::class, 'show'])   ->name('assessments.show');
    Route::post('/assessments/{id}/submit',[MenteeAssessmentController::class, 'submit'])->name('assessments.submit');

    // Community
    Route::get( '/community',                    [MenteeCommunityController::class, 'index'])->name('community.index');
    Route::post('/community/messages/{message}/like', [MessageController::class, 'like'])->name('community.messages.like');
    Route::delete('/community/messages/{message}',    [MessageController::class, 'destroy'])->name('community.messages.destroy');
    Route::get( '/community/{channel:slug}',     [MenteeCommunityController::class, 'show'])->name('community.show');
    Route::post('/community/{channel:slug}/join',    [MenteeCommunityController::class, 'join'])->name('community.join');
    Route::post('/community/{channel:slug}/leave',   [MenteeCommunityController::class, 'leave'])->name('community.leave');
    Route::post('/community/{channel:slug}/invite',  [ChannelController::class, 'invite'])->name('community.invite');
    Route::delete('/community/{channel:slug}/members/{user}', [ChannelController::class, 'removeMember'])->name('community.members.remove');
    Route::delete('/community/{channel:slug}',   [ChannelController::class, 'destroy'])->name('community.destroy');
    Route::post('/community/{channel:slug}/messages',[MessageController::class, 'store'])->name('community.messages.store');

    // Jobs
    Route::get('/jobs', [MenteeJobController::class, 'index'])->name('jobs');
    Route::get('/jobs/{id}', [MenteeJobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{id}/apply', [MenteeJobController::class, 'apply'])->name('jobs.apply');
});

/*
|=============================================================
|  SHARED AUTH ROUTES (mentor + mentee)
|=============================================================
*/
Route::middleware('auth')->group(function () {

    // Generic dashboard redirect based on role
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'admin')  return redirect()->route('admin.dashboard');
        if ($role === 'mentor') return redirect()->route('mentor.dashboard');
        return redirect()->route('mentee.dashboard');
    })->name('dashboard');

    // Profile (shared — lets both roles update basic account info)
    Route::get( '/account',         fn() => view('account.settings', ['user' => auth()->user()]))->name('account');
    Route::put( '/account',         [LoginController::class, 'updateAccount'])->name('account.update');
    Route::post('/account/avatar',  [LoginController::class, 'updateAvatar']) ->name('account.avatar');
    Route::put( '/account/password',[LoginController::class, 'changePassword'])->name('account.password');

    // Session notes (mentor writes, mentee can view if shared)
    Route::get('/sessions/{id}/notes', [MentorSessionController::class, 'notes'])->name('sessions.notes.show');

    // Video call token (works for both roles)
    Route::get('/sessions/{id}/video-token', [MentorSessionController::class, 'videoToken'])->name('sessions.video-token');
});

/*
|=============================================================
|  ADMIN ROUTES  (from your existing web.php — kept as-is)
|=============================================================
*/
Route::get( '/admin',       [AdminController::class, 'showLogin'])->name('admin');
Route::get( '/admin/login', [AdminController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])    ->name('admin.login.post');
Route::post('/admin/logout',[AdminController::class, 'logout'])   ->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Admin Profile ─────────────────────────────────────────
    Route::get('/profile',                 [AdminProfileController::class, 'show'])           ->name('profile.show');
    Route::put('/profile',                 [AdminProfileController::class, 'update'])         ->name('profile.update');
    Route::get('/profile/password',        [AdminProfileController::class, 'editPassword'])   ->name('profile.password');
    Route::put('/profile/password',        [AdminProfileController::class, 'updatePassword']) ->name('profile.password.update');

    // ── Notifications ─────────────────────────────────────────
    Route::get('/notifications',           [NotificationController::class, 'index'])    ->name('notifications.index');
    Route::post('/notifications/seen',     [NotificationController::class, 'markSeen']) ->name('notifications.seen');

    // ── Mentor Approvals ──────────────────────────────────────
    Route::prefix('mentors')->name('mentors.')->group(function () {
        Route::get('/approvals',                 [MentorApprovalController::class, 'index'])        ->name('approvals');
        Route::get('/pending-changes',           [MentorApprovalController::class, 'pendingChanges'])->name('pending-changes');
        Route::get('/deleted',                   [UserController::class, 'mentorTrashed'])          ->name('trashed');
        Route::get('/{mentor}',                  [MentorApprovalController::class, 'show'])         ->name('review');
        Route::post('/{mentor}/approve',         [MentorApprovalController::class, 'approve'])      ->name('approve');
        Route::post('/{mentor}/reject',          [MentorApprovalController::class, 'reject'])       ->name('reject');
        Route::post('/{mentor}/suspend',         [MentorApprovalController::class, 'suspend'])      ->name('suspend');
        Route::post('/{mentor}/reinstate',       [MentorApprovalController::class, 'reinstate'])    ->name('reinstate');
        Route::post('/changes/{change}/approve', [MentorApprovalController::class, 'approveChange'])->name('approve-change');
        Route::post('/changes/{change}/reject',  [MentorApprovalController::class, 'rejectChange']) ->name('reject-change');
        Route::delete('/{user}',                 [MentorApprovalController::class, 'destroy'])      ->name('destroy');
        Route::post('/{id}/restore',             [MentorApprovalController::class, 'restore'])      ->name('restore');
    });

    // ── Admin Create/Edit Mentor & Mentee ─────────────────────
    Route::get( '/mentor/create',        [AdminOnboardingController::class, 'createMentor'])->name('mentors.create');
    Route::post('/mentor',               [AdminOnboardingController::class, 'storeMentor']) ->name('mentor.store');
    Route::get( '/mentor/{mentor}/edit', [AdminOnboardingController::class, 'editMentor'])  ->name('mentor.edit');
    Route::put( '/mentor/{mentor}',      [AdminOnboardingController::class, 'updateMentor'])->name('mentor.update');

    Route::get( '/mentee/create',        [AdminOnboardingController::class, 'createMentee'])->name('mentees.create');
    Route::post('/mentee',               [AdminOnboardingController::class, 'storeMentee']) ->name('mentee.store');
    Route::get( '/mentee/{mentee}/edit', [AdminOnboardingController::class, 'editMentee'])  ->name('mentee.edit');
    Route::put( '/mentee/{mentee}',      [AdminOnboardingController::class, 'updateMentee'])->name('mentee.update');

    // ── Mentee List ───────────────────────────────────────────
    Route::prefix('mentees')->name('mentees.')->group(function () {
        Route::get('/',                        [UserController::class, 'menteeIndex'])        ->name('index');
        Route::get('/deleted',                 [UserController::class, 'menteeTrashed'])      ->name('trashed');
        Route::get('/{mentee}/journey',        [UserController::class, 'menteeJourney'])      ->name('journey');
        Route::get('/{mentee}',                [UserController::class, 'menteeShow'])         ->name('show');
        Route::post('/{mentee}/toggle-status', [UserController::class, 'menteeToggleStatus'])->name('toggle-status');
        Route::post('/{mentee}/assign-mentor', [UserController::class, 'menteeAssignMentor'])->name('assign-mentor');
        Route::delete('/{mentee}',             [UserController::class, 'menteeDestroy'])      ->name('destroy');
    });

    Route::prefix('mentors')->name('mentors.')->group(function () {
        Route::get('/',                        [UserController::class, 'mentorIndex'])        ->name('index');
        Route::post('/{mentor}/toggle-status', [UserController::class, 'mentorToggleStatus'])->name('toggle-status');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/{id}/restore',        [UserController::class, 'restore'])    ->name('restore');
        Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('force-delete');
    });

    // ── Sessions ──────────────────────────────────────────────
    Route::post('sessions/{session}/confirm',  [ConsultationSessionController::class, 'confirm'])   ->name('sessions.confirm');
    Route::post('sessions/{session}/cancel',   [ConsultationSessionController::class, 'cancel'])    ->name('sessions.cancel');
    Route::post('sessions/{session}/complete', [ConsultationSessionController::class, 'complete'])  ->name('sessions.complete');
    Route::post('sessions/{session}/no-show',  [ConsultationSessionController::class, 'markNoShow'])->name('sessions.no-show');
    Route::post('sessions/{session}/notes',    [ConsultationSessionController::class, 'addNote'])   ->name('sessions.add-note');
    Route::get( 'sessions/export',             [ConsultationSessionController::class, 'export'])    ->name('sessions.export');
    Route::post('sessions/{session}/invoice',  [AdminSessionInvoiceController::class, 'generate'])->name('sessions.invoice');
    Route::get( 'session-invoices/{invoice}/download', [AdminSessionInvoiceController::class, 'download'])->name('session-invoices.download');
    Route::resource('sessions', ConsultationSessionController::class);
    Route::get( 'sessions/{session}/review',   [SessionReviewController::class, 'create'])->name('sessions.review.create');
    Route::post('sessions/{session}/review',   [SessionReviewController::class, 'store']) ->name('sessions.review.store');

    Route::resource('assessments', AssessmentController::class);

    // ── Wallet ────────────────────────────────────────────────
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/',                        [WalletTransactionController::class, 'index'])    ->name('index');
        Route::get('/users',                   [WalletTransactionController::class, 'users'])    ->name('users');
        Route::get('/customer/{user}',         [WalletTransactionController::class, 'showUser'])->name('customer.show');
        Route::post('/adjust/{type}/{id}',     [WalletTransactionController::class, 'adjust'])  ->name('adjust');
        Route::post('/transfer',               [WalletTransactionController::class, 'transfer'])->name('transfer');
    });

    // ── Withdrawals ───────────────────────────────────────────
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/',                              [WithdrawalRequestController::class, 'index'])  ->name('index');
        Route::post('/{withdrawal}/approve',         [WithdrawalRequestController::class, 'approve'])->name('approve');
        Route::post('/{withdrawal}/reject',          [WithdrawalRequestController::class, 'reject']) ->name('reject');
    });

    // ── Call Logs ─────────────────────────────────────────────
    Route::prefix('call-logs')->name('call-logs.')->group(function () {
        Route::get('/',              [VideoCallLogController::class, 'index'])      ->name('index');
        Route::get('/{videoCallLog}',[VideoCallLogController::class, 'show'])       ->name('show');
        Route::delete('/{videoCallLog}',[VideoCallLogController::class, 'destroy'])->name('destroy');
        Route::delete('/',           [VideoCallLogController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::get('/export',        [VideoCallLogController::class, 'export'])     ->name('export');
    });

    // ── Curriculum / Career Streams ───────────────────────────
    Route::resource('mentor-approvals', MentorApprovalController::class);
    Route::prefix('curriculum')->name('curriculum.')->group(function () {
        Route::get('/',                           [CurriculumController::class, 'streams'])          ->name('streams');
        Route::post('/streams',                   [CurriculumController::class, 'storeStream'])      ->name('streams.store');
        Route::put('/streams/{stream}',           [CurriculumController::class, 'updateStream'])     ->name('streams.update');
        Route::delete('/streams/{stream}',        [CurriculumController::class, 'destroyStream'])    ->name('streams.destroy');

        Route::get('/streams/{stream}/months',    [CurriculumController::class, 'months'])           ->name('months');
        Route::post('/streams/{stream}/months',   [CurriculumController::class, 'storeMonth'])       ->name('months.store');
        Route::put('/months/{month}',             [CurriculumController::class, 'updateMonth'])      ->name('months.update');
        Route::delete('/months/{month}',          [CurriculumController::class, 'destroyMonth'])     ->name('months.destroy');

        Route::get('/months/{month}/weeks',       [CurriculumController::class, 'weeks'])            ->name('weeks');
        Route::post('/months/{month}/weeks',      [CurriculumController::class, 'storeWeek'])        ->name('weeks.store');
        Route::put('/weeks/{week}',               [CurriculumController::class, 'updateWeek'])       ->name('weeks.update');
        Route::delete('/weeks/{week}',            [CurriculumController::class, 'destroyWeek'])      ->name('weeks.destroy');

        Route::post('/weeks/{week}/tasks',        [CurriculumController::class, 'storeTask'])        ->name('tasks.store');
        Route::put('/tasks/{task}',               [CurriculumController::class, 'updateTask'])       ->name('tasks.update');
        Route::delete('/tasks/{task}',            [CurriculumController::class, 'destroyTask'])      ->name('tasks.destroy');

        Route::post('/weeks/{week}/mcqs',         [CurriculumController::class, 'storeMcq'])         ->name('mcqs.store');
        Route::put('/mcqs/{mcq}',                 [CurriculumController::class, 'updateMcq'])        ->name('mcqs.update');
        Route::delete('/mcqs/{mcq}',              [CurriculumController::class, 'destroyMcq'])       ->name('mcqs.destroy');

        Route::get('/enrollments',                [CurriculumController::class, 'enrollments'])       ->name('enrollments.index');
        Route::get('/enrollments/{enrollment}',   [CurriculumController::class, 'enrollmentShow'])    ->name('enrollments.show');
        Route::get('/streams/{stream}/progress',  [CurriculumController::class, 'progressOverview'])  ->name('progress');
        Route::patch('/submissions/{progress}/review', [CurriculumController::class, 'reviewSubmission'])->name('submissions.review');
    });

    // ── Admin Journey (mentee-side view) ──────────────────────
    Route::prefix('journey')->name('mentee.journey.')->group(function () {
        Route::get('/',                             [AdminJourneyController::class, 'index'])       ->name('index');
        Route::get('/month/{month}',                [AdminJourneyController::class, 'month'])       ->name('month');
        Route::get('/week/{week}',                  [AdminJourneyController::class, 'week'])        ->name('week');
        Route::post('/tasks/{task}/complete',       [AdminJourneyController::class, 'completeTask'])->name('task.complete');
        Route::post('/mcqs/{mcq}/answer',           [AdminJourneyController::class, 'answerMcq'])  ->name('mcq.answer');
        Route::post('/weeks/{week}/checkin',        [AdminJourneyController::class, 'submitCheckin'])->name('checkin');
        Route::post('/checkins/{checkin}/reply',    [AdminJourneyController::class, 'replyCheckin'])->name('checkin.reply');
    });

    // ── Community ─────────────────────────────────────────────
    Route::prefix('community')->name('community.')->group(function () {
        Route::get('/',                      [ChannelController::class, 'index'])  ->name('index');
        Route::get('/create',                [ChannelController::class, 'create']) ->name('create');
        Route::post('/',                     [ChannelController::class, 'store'])  ->name('store');
        Route::post('/messages/{message}/like', [MessageController::class, 'like'])   ->name('messages.like');
        Route::delete('/messages/{message}',    [MessageController::class, 'destroy'])->name('messages.destroy');
        Route::get('/{channel:slug}',        [ChannelController::class, 'show'])   ->name('show');
        Route::post('/{channel:slug}/join',  [ChannelController::class, 'join'])   ->name('join');
        Route::post('/{channel:slug}/leave', [ChannelController::class, 'leave'])  ->name('leave');
        Route::post('/{channel:slug}/invite',[ChannelController::class, 'invite'])->name('invite');
        Route::delete('/{channel:slug}/members/{user}', [ChannelController::class, 'removeMember'])->name('members.remove');
        Route::delete('/{channel:slug}',     [ChannelController::class, 'destroy'])->name('destroy');
        Route::post('/{channel:slug}/messages', [MessageController::class, 'store'])  ->name('messages.store');
    });

    // ── Wellness ──────────────────────────────────────────────
    Route::prefix('wellness')->name('wellness.')->group(function () {
        Route::get('/',                          [WellnessSurveyController::class, 'index'])  ->name('index');
        Route::get('/create',                    [WellnessSurveyController::class, 'create']) ->name('create');
        Route::post('/',                         [WellnessSurveyController::class, 'store'])  ->name('store');
        Route::get('/{wellnessSurvey}',          [WellnessSurveyController::class, 'show'])   ->name('show');
        Route::post('/{wellnessSurvey}/respond', [WellnessSurveyController::class, 'respond'])->name('respond');
        Route::get('/{wellnessSurvey}/results',  [WellnessSurveyController::class, 'results'])->name('results');
        Route::delete('/{wellnessSurvey}',       [WellnessSurveyController::class, 'destroy'])->name('destroy');
    });

    // ── Quizzes ───────────────────────────────────────────────
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/',                                         [QuizController::class, 'index'])  ->name('index');
        Route::get('/create',                                   [QuizController::class, 'create']) ->name('create');
        Route::post('/',                                        [QuizController::class, 'store'])  ->name('store');
        Route::get('/{quiz}',                                   [QuizController::class, 'show'])   ->name('show');
        Route::post('/{quiz}/attempt',                          [QuizController::class, 'attempt'])->name('attempt');
        Route::post('/{quiz}/attempt/{attempt}/submit',         [QuizController::class, 'submit']) ->name('submit');
        Route::get('/{quiz}/attempt/{attempt}/result',          [QuizController::class, 'result']) ->name('result');
        Route::delete('/{quiz}',                                [QuizController::class, 'destroy'])->name('destroy');
    });

    // ── Plans ─────────────────────────────────────────────────
    Route::get('plans/restore/{id}',          [PlanController::class, 'restore'])    ->name('plans.restore');
    Route::post('plans/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('plans.toggle-status');
    Route::post('plans/reorder',              [PlanController::class, 'reorder'])    ->name('plans.reorder');
    Route::resource('plans', PlanController::class);

    // ── Plan subscriptions & invoices ─────────────────────────
    Route::get('subscriptions',                        [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}',         [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('subscriptions/{subscription}/invoice',[SubscriptionController::class, 'generateInvoice'])->name('subscriptions.invoice');
    Route::get('invoices',                             [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}',                   [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/print',             [AdminInvoiceController::class, 'print'])->name('invoices.print');
    Route::get('invoices/{invoice}/download',          [AdminInvoiceController::class, 'download'])->name('invoices.download');

    // ── Jobs ──────────────────────────────────────────────────
    Route::post('jobs/{job}/toggle-status', [JobListingController::class, 'toggleStatus'])->name('jobs.toggle-status');
    Route::resource('jobs', JobListingController::class);

    // ── Settings ──────────────────────────────────────────────
    Route::prefix('/settings')->name('settings.')->group(function () {
        Route::get('/',              [AppSettingsController::class, 'index'])       ->name('index');
        Route::post('/update',       [AppSettingsController::class, 'update'])      ->name('update');
        Route::get('/test-email',    [AppSettingsController::class, 'testEmail'])   ->name('test-email');
        Route::get('/test-agora',    [AppSettingsController::class, 'testAgora'])   ->name('test-agora');
        Route::get('/zoom/connect',  [AppSettingsController::class, 'zoomConnect']) ->name('zoom-connect');
        Route::get('/zoom/callback', [AppSettingsController::class, 'zoomCallback'])->name('zoom-callback');
        Route::get('/google/connect', [AppSettingsController::class, 'googleConnect'])->name('google-connect');
        Route::get('/google/callback',[AppSettingsController::class, 'googleCallback'])->name('google-callback');
    });

    // ── Activity Logs ─────────────────────────────────────────
    Route::get('logs',           [ActivityLogController::class, 'index'])      ->name('logs.index');
    Route::get('logs/latest',    [ActivityLogController::class, 'latest'])     ->name('logs.latest');
    Route::get('logs/export',    [ActivityLogController::class, 'export'])     ->name('logs.export');
    Route::post('logs/purge',    [ActivityLogController::class, 'bulkDestroy'])->name('logs.bulk-destroy');
    Route::get('logs/{log}',     [ActivityLogController::class, 'show'])       ->name('logs.show');
    Route::delete('logs/{log}',  [ActivityLogController::class, 'destroy'])    ->name('logs.destroy');
});

