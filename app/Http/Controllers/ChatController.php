<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = [
            'user' => auth()->user() ? auth()->user()->name : 'Guest',
            'text' => $request->input('message'),
            'timestamp' => now()->toIso8601String()
        ];
        
        broadcast(new MessageSent($message));
        
        return response()->json(['status' => 'Message sent!', 'message' => $message]);
    }
}