<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class BankBlack extends Model
{
    const UPDATED_AT = null;
    protected $table = 'bank_black';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
