<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordHistory extends Model
{
    public $timestamps = false;

    protected $table = 'records_history';

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    public function literature(): BelongsTo
    {
        return $this->belongsTo(Literature::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }

    public static function recordFrom(Record $record, string $action, ?int $savedByUserId = null): void
    {
        static::create([
            'record_id' => $record->id,
            'action' => $action,
            'saved_by_user_id' => $savedByUserId,
            'literature_id' => $record->literature_id,
            'species_id' => $record->species_id,
            'municipality_id' => $record->municipality_id,
            'memo' => $record->memo,
            'tag_id' => $record->tag_id,
            'user_id' => $record->user_id,
            'is_collected' => $record->is_collected,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'deleted_at' => $record->deleted_at,
            'recorded_at' => now(),
        ]);
    }
}
