<?php

namespace App\Services;

use App\Models\ImageUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\Dropbox\Exceptions\BadRequest as DropboxBadRequest;

/**
 * 負責將圖片上傳至雲端儲存（Dropbox / S3 / local）
 * 並將上傳紀錄寫入 image_uploads 資料表。
 */
class ImageUploadService
{
    /**
     * 上傳圖片並建立資料庫紀錄。
     *
     * @param  UploadedFile  $file    前端傳入的圖片檔案
     * @param  int           $userId  上傳者的 user id（未登入時可為 0）
     */
    public function store(UploadedFile $file, int $userId): ImageUpload
    {
        // 依 FILESYSTEM_DISK 環境變數決定儲存目標（dropbox / s3 / public）
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

    /**
     * 根據 disk 類型產生可公開存取的圖片 URL。
     *
     * Dropbox 需透過 API 建立分享連結，URL 參數 ?raw=1 可讓瀏覽器
     * 直接顯示圖片而非 Dropbox 預覽頁面。
     */
    private function resolveUrl(string $disk, string $path): string
    {
        if ($disk === 'dropbox') {
            $client = new DropboxClient(
                config('filesystems.disks.dropbox.authorization_token')
            );

            // Dropbox API 路徑必須以 '/' 開頭
            $dropboxPath = str_starts_with($path, '/') ? $path : '/' . $path;

            try {
                // 為剛上傳的檔案建立公開分享連結
                $response = $client->createSharedLinkWithSettings($dropboxPath);
            } catch (DropboxBadRequest) {
                // 該檔案已存在分享連結時，直接取得現有的連結
                $links    = $client->listSharedLinks($dropboxPath);
                $response = $links['links'][0] ?? ['url' => ''];
            }

            // ?dl=0 為 Dropbox 預覽頁，替換為 ?raw=1 使 <img> 可直接載入圖片
            return str_replace('?dl=0', '?raw=1', $response['url'] ?? '');
        }

        return match ($disk) {
            's3'    => Storage::disk('s3')->url($path),
            default => Storage::disk('public')->url($path),
        };
    }
}
