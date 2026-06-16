<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    protected $table = 'landmarks';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'prefecture_id' => 'integer',
        'lat' => 'float',
        'lon' => 'float',
        'sort_order' => 'integer',
    ];
}
