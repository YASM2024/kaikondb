<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Literature extends Model
{
    use SoftDeletes;

    protected $table = 'literatures';
    protected $guarded = ['id'];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'literature_order', 'literature_id', 'order_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'literature_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'literature_tag', 'literature_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * @return list<int>
     */
    public function getTagIds(): array
    {
        $pivotTagIds = $this->relationLoaded('tags')
            ? $this->tags->pluck('id')->all()
            : $this->tags()->pluck('tags.id')->all();

        return array_values(array_unique(array_map('intval', $pivotTagIds)));
    }

    public function getPrimaryTagId(): ?int
    {
        $ids = $this->getTagIds();

        return $ids !== [] ? $ids[0] : null;
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $tagIds = $user->getTagIds();
        if ($tagIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereHas('tags', fn (Builder $tagQuery) => $tagQuery->whereIn('tags.id', $tagIds));
    }

    public function scopeSelectSummary($query)
    {
        return $query->selectRaw("CONCAT(author,',',year,'.',journal_name_ja,'.',vol_no,':',page) AS summary");
    }

    public function scopeSelectSummaryShort($query)
    {
        return $query->selectRaw("CONCAT(author,',',year) AS summary_short");
    }

}
