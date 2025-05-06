<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    //use HasFactory;
    protected $table = 'user_login_logs';
    protected $guarded = ['*']; 
    
}
