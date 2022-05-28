<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class MemberDealing extends Model
{
    public $timestamps = false;
    protected $table = 'member_dealing';
    protected $guarded = [];
    const UPDATED_AT = null;

}
