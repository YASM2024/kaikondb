<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeratorTag extends Model
{
    use HasFactory;
    protected $table = 'moderator_tags';
    protected $guarded = ['id',]; 
    
}
