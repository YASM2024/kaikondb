<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    //use HasFactory;
    protected $table = 'licenses';
    protected $guarded = ['id',];

    public $timestamps = false;
    
}
