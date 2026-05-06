<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageUpload extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'disk',
        'path',
        'url',
        'size',
        'mime_type',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
