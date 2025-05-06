<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    //use HasFactory;
    
    protected $table = 'access_logs';
    protected $guarded = ['id',]; 

}
