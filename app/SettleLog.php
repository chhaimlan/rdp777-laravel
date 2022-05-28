<?php
/**
 * Created by PhpStorm.
 * User: Yonug<820355121@qq.com>
 * Date: 2019/11/7
 * Time: 15:41
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
class SettleLog extends Model
{
    public $timestamps = false;
    protected $table = 'settle_logs';
    protected $guarded = [];
}
