<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class PlaySet  extends Model
{
    protected $table = 'play_sets';
    protected $guarded = [];
    const UPDATED_AT = null;
    public $timestamps = false;
}

