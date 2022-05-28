<?php
/**
 * Created by PhpStorm.
 * User: Yonug<820355121@qq.com>
 * Date: 2019/11/7
 * Time: 15:41
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
class SysLog extends Model
{
    protected $table = 'sys_logs';
    const UPDATED_AT = null;
    protected static function boot()
    {
        parent::boot();
    }
}
