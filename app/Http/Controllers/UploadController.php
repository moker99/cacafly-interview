<?php

namespace App\Http\Controllers;

use App\Models\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload.index');
    }

    /**
     * Upload one image and return JSON status.
     * Called per-file from the frontend via fetch().
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'],   // max 10 MB
        ]);

        $file = $request->file('image');
        $disk = config('filesystems.default');   // dropbox / s3 / public
        $path = $file->store('uploads', $disk);

        $url = match ($disk) {
            's3'      => Storage::disk('s3')->url($path),
            'dropbox' => 'https://www.dropbox.com/home/uploads/' . basename($path),
            default   => Storage::disk('public')->url($path),
        };

        $record = ImageUpload::create([
            'user_id'       => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'disk'          => $disk,
            'path'          => $path,
            'url'           => $url,
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'status'        => 'uploaded',
        ]);

        return response()->json([
            'success' => true,
            'id'      => $record->id,
            'name'    => $record->original_name,
            'url'     => $url,
        ]);
    }
}
