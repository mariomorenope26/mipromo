<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityAlbumPhoto extends Model
{
    protected $fillable = [
        'album_id',
        'user_id',
        'caption',
        'image_path',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(CommunityAlbum::class, 'album_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
