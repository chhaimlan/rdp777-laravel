<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
class Agent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    // 最好要设置成公共的,方便 直接读取Auth::user()->guard_name,不然就要通过Guard::getDefaultName(Auth::user());
    public $guard_name = 'agent';


    protected $guarded = ['pass'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token','token_expire_time'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'leaf' => 'boolean',
        'point' => 'double'
    ];

}
