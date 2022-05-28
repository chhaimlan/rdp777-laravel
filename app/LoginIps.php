<?php


namespace App;
use Illuminate\Database\Eloquent\Model;

class LoginIps extends Model
{
    protected $table = 'login_ips';
    protected $guarded = [];
    const UPDATED_AT = null;
}
