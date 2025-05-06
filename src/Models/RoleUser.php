<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

//use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;

class RoleUser extends Model
{
    public $timestamps = false;
    
    protected $table = 'role_user';
    protected $guarded = ['id',];

}
