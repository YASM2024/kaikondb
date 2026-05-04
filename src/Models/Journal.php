<?php

namespace Kaikon2\Kaikondb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Journal extends Model
{
    //use HasFactory;
    protected $table = 'journals';

    protected $fillable = [
        'journal_code',
        'journal_name_ja',
        'journal_name_en',
        'category',
        'publisher',
        'url',
        'provided_by',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'category' => 'integer',
        'journal_code' => 'integer',
    ];
}
