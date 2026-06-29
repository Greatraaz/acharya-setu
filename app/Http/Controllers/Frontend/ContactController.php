<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('frontend.contact');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:3000',
        ]);

        try {
            Mail::to(config('mail.from.address'))->send(new ContactFormMail($data));
        } catch (\Throwable $e) {
            \Log::error('Contact mail failed: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => "Message sent! We'll reply within 24 hours."]);
        }

        return back()->with('success', "Message sent! We'll reply within 24 hours.");
    }
}