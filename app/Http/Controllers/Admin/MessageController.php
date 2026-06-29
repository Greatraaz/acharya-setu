<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class MessageController extends Controller
{
 
    public function store(Request $request, Channel $channel)
    {
        abort_if(!$channel->isMember(Auth::user()) && $channel->type === 'private', 403);
 
        $request->validate(['body' => 'required|string|max:5000', 'parent_id' => 'nullable|exists:messages,id']);
 
        Message::create([
            'channel_id' => $channel->id,
            'user_id'    => Auth::id(),
            'body'       => $request->body,
            'parent_id'  => $request->parent_id,
        ]);
 
        return back();
    }
 
    public function destroy(Message $message)
    {
        abort_unless($message->user_id === Auth::id() || Auth::user()->is_admin, 403);
        $message->delete();
        return back()->with('success', 'Message deleted.');
    }
}