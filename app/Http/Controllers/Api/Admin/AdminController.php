<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User,Session,Task,Video,JobListing,Quiz,QuizQuestion,PremiumPlan,CommunityChannel,Assessment,Notification};
use Illuminate\Http\{Request,JsonResponse};

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json(['stats' => [
            'total_users'     => User::count(),
            'total_mentors'   => User::where('role','mentor')->count(),
            'total_mentees'   => User::where('role','mentee')->count(),
            'total_sessions'  => Session::count(),
            'active_sessions' => Session::where('status','upcoming')->count(),
            'total_videos'    => Video::count(),
            'total_jobs'      => JobListing::count(),
            'total_tasks'     => Task::count(),
            'new_users_today' => User::whereDate('created_at',today())->count(),
            'revenue_today'   => Session::whereDate('created_at',today())->sum('amount_paid'),
        ]]);
    }

    // Users
    public function users(Request $request): JsonResponse
    {
        $q=User::query();
        if($r=$request->role)   $q->where('role',$r);
        if($s=$request->search) $q->where(fn($x)=>$x->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        return response()->json($q->orderByDesc('created_at')->paginate(20));
    }
    public function showUser(string $id): JsonResponse { return response()->json(['user'=>User::with(['sessionsAsMentee','sessionsAsMentor','tasks','walletTransactions'])->findOrFail($id)]); }
    public function updateUser(Request $request, string $id): JsonResponse { $u=User::findOrFail($id); $u->update($request->validate(['name'=>'sometimes|string','role'=>'sometimes|in:mentor,mentee,admin','is_active'=>'sometimes|boolean','subscription_plan'=>'sometimes|string','mentor_status'=>'sometimes|in:pending,approved,rejected'])); return response()->json(['user'=>$u]); }
    public function deleteUser(string $id): JsonResponse { User::findOrFail($id)->update(['is_active'=>false]); return response()->json(['message'=>'User deactivated']); }

    // Sessions
    public function sessions(Request $request): JsonResponse { $q=Session::with(['mentor:id,name','mentee:id,name']); if($s=$request->status) $q->where('status',$s); return response()->json($q->orderByDesc('scheduled_at')->paginate(20)); }

    // Jobs
    public function jobs(): JsonResponse { return response()->json(['jobs'=>JobListing::orderByDesc('created_at')->get()]); }
    public function createJob(Request $request): JsonResponse { return response()->json(['job'=>JobListing::create($request->validate(['title'=>'required|string','company'=>'required|string','location'=>'nullable|string','salary_range'=>'nullable|string','type'=>'required|in:full_time,part_time,internship,contract','mode'=>'required|in:remote,onsite,hybrid','description'=>'nullable|string','requirements'=>'nullable|string','apply_url'=>'nullable|url','category'=>'nullable|string']))],201); }
    public function updateJob(Request $request, string $id): JsonResponse { $j=JobListing::findOrFail($id); $j->update($request->all()); return response()->json(['job'=>$j]); }
    public function deleteJob(string $id): JsonResponse { JobListing::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }

    // Videos
    public function videos(): JsonResponse { return response()->json(['videos'=>Video::with('mentor:id,name')->orderByDesc('created_at')->get()]); }
    public function deleteVideo(string $id): JsonResponse { Video::findOrFail($id)->update(['is_active'=>false]); return response()->json(['message'=>'Hidden']); }

    // Quizzes
    public function quizzes(): JsonResponse { return response()->json(['quizzes'=>Quiz::withCount('questions')->get()]); }
    public function createQuiz(Request $request): JsonResponse { return response()->json(['quiz'=>Quiz::create($request->validate(['title'=>'required|string','description'=>'nullable|string','category'=>'required|string','level'=>'required|in:beginner,intermediate,advanced','time_limit_minutes'=>'sometimes|integer']))],201); }
    public function addQuestion(Request $request, string $quizId): JsonResponse
    {
        $d=$request->validate(['question'=>'required|string','options'=>'required|array|min:2','correct_index'=>'required|integer','explanation'=>'nullable|string']);
        $q=QuizQuestion::create(array_merge($d,['quiz_id'=>$quizId,'options'=>json_encode($d['options'])]));
        Quiz::where('id',$quizId)->update(['question_count'=>QuizQuestion::where('quiz_id',$quizId)->count()]);
        return response()->json(['question'=>$q],201);
    }

    // Plans
    public function plans(): JsonResponse { return response()->json(['plans'=>PremiumPlan::all()]); }
    public function createPlan(Request $request): JsonResponse { return response()->json(['plan'=>PremiumPlan::create($request->validate(['name'=>'required|string','slug'=>'required|string|unique:premium_plans','price_monthly'=>'required|numeric','price_yearly'=>'sometimes|numeric','sessions_per_month'=>'sometimes|integer','features'=>'nullable|array']))],201); }

    // Assessments
    public function assessments(): JsonResponse { return response()->json(['assessments'=>Assessment::orderBy('month')->get()]); }
    public function createAssessment(Request $request): JsonResponse { return response()->json(['assessment'=>Assessment::create($request->validate(['title'=>'required|string','month'=>'required|integer|min:1|max:6','description'=>'nullable|string','questions'=>'required|array']))],201); }

    // Community channels
    public function communityChannels(): JsonResponse { return response()->json(['channels'=>CommunityChannel::withCount('messages')->get()]); }
    public function createChannel(Request $request): JsonResponse { return response()->json(['channel'=>CommunityChannel::create($request->validate(['name'=>'required|string','description'=>'nullable|string','icon'=>'sometimes|string','color'=>'sometimes|string']))],201); }

    // Broadcast
    public function broadcastNotification(Request $request): JsonResponse
    {
        $d=$request->validate(['title'=>'required|string','body'=>'required|string','type'=>'sometimes|string','role'=>'nullable|in:mentor,mentee']);
        $q=User::where('is_active',true); if($d['role']??null) $q->where('role',$d['role']);
        $users=$q->pluck('id');
        $notifs=$users->map(fn($uid)=>['id'=>\Illuminate\Support\Str::uuid(),'user_id'=>$uid,'type'=>$d['type']??'broadcast','title'=>$d['title'],'body'=>$d['body'],'is_read'=>false,'created_at'=>now(),'updated_at'=>now()]);
        Notification::insert($notifs->toArray());
        return response()->json(['message'=>"Sent to {$users->count()} users"]);
    }
}
