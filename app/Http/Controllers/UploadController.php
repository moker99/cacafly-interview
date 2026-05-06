<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUpload) {}

    public function index()
    {
        return view('upload.index');
    }

    public function store(StoreImageRequest $request): JsonResponse
    {
        $record = $this->imageUpload->store($request->file('image'), Auth::id());

        return response()->json([
            'success' => true,
            'id'      => $record->id,
            'name'    => $record->original_name,
            'url'     => $record->url,
        ]);
    }
}
