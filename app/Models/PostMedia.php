<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostMedia extends Model
{
    protected $table = 'post_media';

    protected $fillable = [
        'post_id',
        'user_id',
        'file_path',
        'file_type',
        'file_size',
        'meta',
        'order',
    ];

    protected $casts = [
        'meta' => 'array',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the post this media belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user (uploader) of this media.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the URL to access this media.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
