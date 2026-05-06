<?php

namespace App\Services;

use App\Models\ImageUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    public function store(UploadedFile $file, int $userId): ImageUpload
    {
        $disk = config('filesystems.default');
        $path = $file->store('uploads', $disk);
        $url  = $this->resolveUrl($disk, $path);

        return ImageUpload::create([
            'user_id'       => $userId,
            'original_name' => $file->getClientOriginalName(),
            'disk'          => $disk,
            'path'          => $path,
            'url'           => $url,
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'status'        => 'uploaded',
        ]);
    }

    private function resolveUrl(string $disk, string $path): string
    {
        return match ($disk) {
            's3'      => Storage::disk('s3')->url($path),
            'dropbox' => 'https://www.dropbox.com/home/uploads/' . basename($path),
            default   => Storage::disk('public')->url($path),
        };
    }
}
