<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Article extends Model
{
    //use HasFactory;
    use SoftDeletes;
    
    protected $table = 'articles';
    protected $guarded = ['id',]; 
 

    protected static function boot()
    {
        parent::boot();
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'article_order', 'article_id', 'order_id');
    }
    
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    //scope
    public function scopeSelectSummary( $query )
    {
        return $this->selectRaw( "CONCAT(author,',',year,'.',journal_name_ja,'.',vol_no,':',page) AS summary" );
    }

    public function scopeSelectSummaryShort( $query )
    {
        return $this->selectRaw( "CONCAT(author,',',year) AS summary_short" );
    }

}
