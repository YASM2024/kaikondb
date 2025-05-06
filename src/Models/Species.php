<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;

class Species extends Model
{
    //use HasFactory;
    protected $table = 'speciess';
    protected $guarded = ['id',];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

}
