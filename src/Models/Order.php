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

    public function species(): HasMany
    {
        return $this->hasMany(Species::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_order', 'order_id', 'article_id');
    }

    public function getCountAttribute()
    {
        return $this->articles_count;
    }
}
