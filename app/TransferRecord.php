<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class TransferRecord extends Model
{
    const UPDATED_AT = null;
    protected $table = 'transfer_record';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

}
