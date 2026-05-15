<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;

class LiteratureOrder extends Model
{
    public $timestamps = false;

    protected $table = 'literature_order';
    protected $guarded = ['id'];
}
