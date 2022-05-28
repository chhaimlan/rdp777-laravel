<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class HbSlots  extends Model
{
    protected $table = 'hb_slots';
    protected $guarded = [];
    const UPDATED_AT = null;
    public $timestamps = false;
}

