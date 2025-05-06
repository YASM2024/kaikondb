<?php

namespace Kaikon2\Kaikondb\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordingStatus extends Model
{
    // use HasFactory;

    protected $table = 'recording_statuses';
    protected $fillable = ['article_id', 'completed_at'];
    public $timestamps = false;
}
