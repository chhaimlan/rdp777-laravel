<?php
/**
 * Created by PhpStorm.
 * User: Yonug<820355121@qq.com>
 * Date: 2019/11/7
 * Time: 15:41
 */

namespace App;
use Illuminate\Database\Eloquent\Model;

class AgentBill extends Model
{
    public $timestamps = false;
    protected $table = 'agent_bills';
    protected $guarded = [];
}
