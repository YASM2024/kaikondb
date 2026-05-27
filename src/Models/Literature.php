<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function scopeSelectSummary($query)
    {
        return $query->selectRaw("CONCAT(author,',',year,'.',journal_name_ja,'.',vol_no,':',page) AS summary");
    }

    public function scopeSelectSummaryShort($query)
    {
        return $query->selectRaw("CONCAT(author,',',year) AS summary_short");
    }

}
