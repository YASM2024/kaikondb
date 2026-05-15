<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;

class Order extends Model
{
    //use HasFactory;
    protected $table = 'orders';
    protected $guarded = ['id',];
    public $timestamps = false;

    public function species(): HasMany
    {
        return $this->hasMany(Species::class);
    }

    public function literatures()
    {
        return $this->belongsToMany(Literature::class, 'literature_order', 'order_id', 'literature_id');
    }

    public function getCountAttribute()
    {
        return $this->literatures_count;
    }
}
