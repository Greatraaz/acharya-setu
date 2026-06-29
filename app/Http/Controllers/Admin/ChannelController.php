<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class ChannelController extends Controller
{
 
    public function index()
    {
        $channels = Channel::where('type', 'public')
            ->orWhereHas('members', fn($q) => $q->where('user_id', Auth::id()))
            ->withCount('allMessages')
            ->latest()
            ->get();
 
        return view('admin.community.index', compact('channels'));
    }
 
    public function create()
    {
        return view('admin.community.create');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:channels',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:10',
            'type'        => 'required|in:public,private',
        ]);
 
        $channel = Channel::create([
            'name'        => $request->name,
            'description' => $request->description,
            'icon'        => $request->icon ?? '💬',
            'type'        => $request->type,
            'created_by'  => Auth::id(),
        ]);
 
        $channel->members()->attach(Auth::id(), ['role' => 'admin']);
 
        return redirect()->route('admin.community.show', $channel->slug)
            ->with('success', 'Channel created!');
    }
 
    public function show(Channel $channel)
    {
        abort_if($channel->type === 'private' && !$channel->isMember(Auth::user()), 403);
 
        $messages = $channel->messages()
            ->with(['user', 'replies.user'])
            ->paginate(30);
 
        $channels = Channel::where('type', 'public')
            ->orWhereHas('members', fn($q) => $q->where('user_id', Auth::id()))
            ->get();
 
        return view('admin.community.show', compact('channel', 'messages', 'channels'));
    }
 
    public function join(Channel $channel)
    {
        abort_if($channel->type === 'private', 403, 'This is a private channel.');
 
        if (!$channel->isMember(Auth::user())) {
            $channel->members()->attach(Auth::id(), ['role' => 'member']);
        }
 
        return back()->with('success', 'Joined channel!');
    }
 
    public function leave(Channel $channel)
    {
        $channel->members()->detach(Auth::id());
        return redirect()->route('admin.community.index')->with('success', 'Left channel.');
    }
 
    public function destroy(Channel $channel)
    {
        abort_unless($channel->created_by === Auth::id(), 403);
        $channel->delete();
        return redirect()->route('admin.community.index')->with('success', 'Channel deleted.');
    }
}