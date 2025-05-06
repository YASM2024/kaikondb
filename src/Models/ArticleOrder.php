<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

//use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;

class ArticleOrder extends Model
{
    public $timestamps = false;
    
    protected $table = 'article_order';
    protected $guarded = ['id',];

}
