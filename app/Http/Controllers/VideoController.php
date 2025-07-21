<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $fileVideo = \App\Models\Video::where('type', 'file')->orderBy('created_at', 'DESC')->first();
        $urlVideo = \App\Models\Video::where('type', 'url')->orderBy('created_at', 'DESC')->first();

        $response = [];
        if ($fileVideo) {
            $response[] = ['url' => $fileVideo->url, 'type' => $fileVideo->type];
        }
        if ($urlVideo) {
            $response[] = ['url' => $urlVideo->url, 'type' => $urlVideo->type];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'sometimes|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo|max:204800', // Max 200MB
            'url' => 'sometimes|url',
        ]);

        if (!$request->hasFile('video') && !$request->has('url')) {
            return response()->json(['message' => 'Either a video file or a URL is required.'], 400);
        }

        $video = new \App\Models\Video();
        $typeToSave = '';

        if ($request->hasFile('video')) {
            $typeToSave = 'file';
            $path = $request->file('video')->store('videos', 'public');
            $video->url = str_replace('/storage/', '/uploads/', Storage::url($path));
            $video->type = $typeToSave;
        } elseif ($request->has('url')) {
            $typeToSave = 'url';
            $video->url = $request->url;
            $video->type = $typeToSave;
        }
        
        // Delete old video of the same type if exists
        $oldVideo = \App\Models\Video::where('type', $typeToSave)->orderBy('created_at', 'DESC')->first();
        if ($oldVideo) {
            // change storage to uploads
            
            if ($oldVideo->type === 'file' && Storage::disk('public')->exists(str_replace('/storage/', '', $oldVideo->url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldVideo->url));
            }
            $oldVideo->delete(); // Delete the old record from the database
        }

        $video->save();

        return response()->json($video, 201);
    }
}

