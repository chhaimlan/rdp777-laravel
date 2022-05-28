<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class PplayInfo  extends Model
{
    protected $table = 'pplay_info';
    protected $guarded = [];
    const UPDATED_AT = null;
    public $timestamps = false;
}

