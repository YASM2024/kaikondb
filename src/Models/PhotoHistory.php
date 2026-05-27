<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoHistory extends Model
{
    public $timestamps = false;

    protected $table = 'photos_history';

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function savedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }

    public static function recordFrom(Photo $photo, string $action, ?int $savedByUserId = null): void
    {
        static::create([
            'photo_id' => $photo->id,
            'action' => $action,
            'saved_by_user_id' => $savedByUserId,
            'photo_title' => $photo->photo_title,
            'user_id' => $photo->user_id,
            'recorded_at' => now(),
        ]);
    }
}
