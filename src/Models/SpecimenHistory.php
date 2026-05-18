<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecimenHistory extends Model
{
    public $timestamps = false;

    protected $table = 'specimens_history';

    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_public' => 'boolean',
        'decimal_latitude' => 'decimal:7',
        'decimal_longitude' => 'decimal:7',
    ];

    public function specimen(): BelongsTo
    {
        return $this->belongsTo(Specimen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function savedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }

    public static function recordFrom(Specimen $specimen, string $action, ?int $savedByUserId = null): void
    {
        static::create([
            'specimen_id' => $specimen->id,
            'action' => $action,
            'saved_by_user_id' => $savedByUserId,
            'user_id' => $specimen->user_id,
            'locality' => $specimen->locality,
            'decimal_latitude' => $specimen->decimal_latitude,
            'decimal_longitude' => $specimen->decimal_longitude,
            'collection_date_text' => $specimen->collection_date_text,
            'collected_by' => $specimen->collected_by,
            'owner' => $specimen->owner,
            'species' => $specimen->species,
            'species_ja' => $specimen->species_ja,
            'sex' => $specimen->sex,
            'identified_by' => $specimen->identified_by,
            'type_status' => $specimen->type_status,
            'image_1' => $specimen->image_1,
            'image_2' => $specimen->image_2,
            'image_3' => $specimen->image_3,
            'preservation_method' => $specimen->preservation_method,
            'repository_institution' => $specimen->repository_institution,
            'repository_catalog_number' => $specimen->repository_catalog_number,
            'is_public' => $specimen->is_public,
            'remarks' => $specimen->remarks,
            'license_id' => $specimen->license_id,
            'created_at' => $specimen->created_at,
            'updated_at' => $specimen->updated_at,
            'recorded_at' => now(),
        ]);
    }
}
