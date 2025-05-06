<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    //use HasFactory;
    protected $table = 'families';
    protected $guarded = ['id',];

    public $timestamps = false;
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
