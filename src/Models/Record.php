<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;

class Record extends Model
{
    //use HasFactory;
    use SoftDeletes;
    
    protected $table = 'records';
    protected $guarded = ['id',]; 

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }


    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
    
    
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
    
}
