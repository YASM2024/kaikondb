<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    //use HasFactory;
    protected $table = 'profiles';
    protected $guarded = ['id',];

}
