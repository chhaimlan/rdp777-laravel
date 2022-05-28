<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    const UPDATED_AT = null;
    protected $table = 'bank_info';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
