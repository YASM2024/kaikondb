<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;


class Photo extends Model
{

    //use HasFactory;
    use SoftDeletes;
    
    protected $table = 'photos';
    
    protected $guarded = ['id'];

    protected $casts = [
        'approved_at' => 'datetime',
        'agreed_at' => 'datetime',
    ];

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'photo_speciess', 'photo_id', 'species_id')
            ->withPivot('sort_order');
    }
}
