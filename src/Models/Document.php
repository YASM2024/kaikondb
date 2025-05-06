<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

// use Kaikon2\Kaikondb\Scopes\ScopeSoftDelete;


class Document extends Model
{
    //use HasFactory;
    use SoftDeletes;
    
    protected $table = 'documents';
    protected $guarded = ['id',]; 

    
    protected static function boot()
    {
        parent::boot();

        // if (Schema::hasTable((new self)->getTable())) {
        //     static::addGlobalScope('customScope', function ($builder) {
        //         // テーブルに特定のカラムが存在する場合のみスコープを適用
        //         if (Schema::hasColumn($builder->getModel()->getTable(), 'some_column')) {
        //             $builder->where('some_column', 'some_value');
        //         }
        //     });
        // }
    }

}
