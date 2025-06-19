<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\Video::orderBy('created_at', 'DESC')->first());
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $video = new \App\Models\Video();
        $video->url = $request->url;
        $video->save();

        return response()->json($video, 201);
    }
}
