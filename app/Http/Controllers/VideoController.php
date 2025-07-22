<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $fileVideo = \App\Models\Video::where('position', 'top')->orderBy('created_at', 'DESC')->first();
        $urlVideo = \App\Models\Video::where('position', 'bottom')->orderBy('created_at', 'DESC')->first();

        $response = [];
        if ($fileVideo) {
            $response[] = ['url' => $fileVideo->url, 'type' => $fileVideo->type, 'position' => $fileVideo->position, 'show' => $fileVideo->show];
        }
        if ($urlVideo) {
            $response[] = ['url' => $urlVideo->url, 'type' => $urlVideo->type, 'position' => $urlVideo->position, 'show' => $urlVideo->show];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        if ($request->video === 'null') {
            $request->merge(['video' => null]);
        }

        $request->validate([
            'video' => [
                'nullable',
                'file',
                'mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo',
                'max:204800'
            ],
            'url' => 'sometimes|url',
            'position' => 'required|string|in:top,bottom',
            'show' => 'sometimes|string|in:true,false',
        ]);

        if ($request->video != null && !$request->hasFile('video') && !$request->filled('url')) {
            return response()->json(['message' => 'Either a video file or a URL is required.'], 400);
        }

        $video = \App\Models\Video::firstOrNew(['position' => $request->position]);

        if ($request->hasFile('video')) {
            // Delete old file if it exists and was a file type
            if ($video->type === 'file' && $video->url && Storage::disk('public')->exists(str_replace('/storage/', '', $video->url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $video->url));
            }

            $path = $request->file('video')->store('videos', 'public');
            $video->url = str_replace('/storage/', '/uploads/', Storage::url($path));
            $video->type = 'file';
        } elseif ($request->filled('url')) {
            $video->url = $request->url;
            $video->type = 'url';
        }

        $video->position = $request->position;
        $video->show = $request->boolean('show', true); // default to true if not set
        $video->save();

        return response()->json($video, 201);
    }

}

