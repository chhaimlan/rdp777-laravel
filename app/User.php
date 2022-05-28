<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\User
 *
 * @property int $id
 * @property int $agent_id 上级id,直属代理为0
 * @property string $sn 用户唯一标记
 * @property float $point 真人点数(钱)
 * @property float $cost_rate 占成
 * @property float $rebate_rate 洗码比例
 * @property int $hide_rebate 是否隐藏洗码
 * @property string|null $nickname
 * @property string|null $avatar 头像
 * @property string $bet_limit 下注组
 * @property int $status 0关闭,1开启
 * @property int $bet_status 下注状态
 * @property string $agent_chain 代理链
 * @property int $is_online 是否在线
 * @property int $commission_type 龙虎抽水类型01
 * @property int $create_id 创建id
 * @property string $password
 * @property int $token_expire_time
 * @property int $login_times 登录次数
 * @property string|null $last_login_time 上次登录时间
 * @property string|null $last_login_ip 上次登录ip
 * @property string|null $last_login_location 上次登录地点
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAgentChain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereBetLimitGroups($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereBetStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCommissionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCostRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereHideRebate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLoginTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRebateRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTokenExpireTime($value)
 * @mixin \Eloquent
 * @property string|null $auth_token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAuthToken($value)
 */

class User extends Authenticatable
{
    use Notifiable;
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email', 'password',
//    ];
        protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
