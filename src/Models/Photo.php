<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;


class Photo extends Model
{

    //use HasFactory;
    use SoftDeletes;
    
    protected $table = 'photos';
    
    protected $guarded = ['id',]; 
}
