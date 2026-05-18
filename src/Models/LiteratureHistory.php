<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiteratureHistory extends Model
{
    public $timestamps = false;

    protected $table = 'literatures_history';

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function literature(): BelongsTo
    {
        return $this->belongsTo(Literature::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }
}
