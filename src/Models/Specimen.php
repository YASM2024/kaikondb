<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Specimen extends Model
{
    use HasFactory;

    protected $table = 'specimens';
    protected $fillable = [
        'user_id', 'locality', 'decimal_latitude', 'decimal_longitude',
        'collection_date_text', 'collected_by', 'owner', 
        'species', 'species_ja', 'sex', 'identified_by',
        'type_status', 'image_1', 'image_2', 'image_3',
        'preservation_method', 'repository_institution',
        'repository_catalog_number', 'remarks', 'license_id', 'platform',
        'account_name', 'account_url', 'is_public',
    ];

    protected $casts = [
        'decimal_latitude'  => 'decimal:7',
        'decimal_longitude' => 'decimal:7',
        'license_id'        => 'integer',
        'is_public'         => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function getImagesAttribute(): array
    {
        return array_values(array_filter([
            $this->image_1,
            $this->image_2,
            $this->image_3,
        ], fn ($v) => !is_null($v) && $v !== ''));
    }

    /**
     * SNS情報をまとめて返す（未設定なら null）
     */
    public function getSocialAccountAttribute(): ?array
    {
        if (!$this->platform && !$this->account_name && !$this->account_url) {
            return null;
        }

        return [
            'platform'     => $this->platform,
            'account_name' => $this->account_name,
            'account_url'  => $this->account_url,
        ];
    }

    /**
     * 検索用スコープ
     */
    public function scopeSpeciesLike($query, string $keyword)
    {
        return $query->where('species', 'like', "%{$keyword}%");
    }
}
