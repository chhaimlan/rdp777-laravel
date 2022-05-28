<?php
/**
 * Created by PhpStorm.
 * User: Yonug<820355121@qq.com>
 * Date: 2020/2/17
 * Time: 9:33
 */

namespace App;

use App\SysLog;
use App\User;

class Logger
{
    public const LOGIN_GROUP = 1;
    public const AGENT_LOGIN = 11;
    public const USER_LOGIN = 12;
    public const LOGOUT = 13;
    public const LOCK_GROUP = 2;
    public const OPEN_LOGIN = 21;
    public const CLOSE_LOGIN = 22;
    public const OPEN_BET = 23;
    public const CLOSE_BET = 24;
    public const POINT_GROUP = 3;
    public const ADD_POINT = 31;
    public const ADD_POINT_BY_ROOT = 32;
    public const REDUCE_POINT = 33;
    public const PLACED_BET = 34;
    public const BET_WIN = 35;
    public const CANCEL_SETTLE = 36;
    public const ACCOUNT_GROUP = 4;
    public const ADD_USER_OR_AGENT = 41;
    public const EDIT_USER = 42;
    public const EDIT_AGENT = 43;
    public const EDIT_PASS_BY_SELF = 44;
    public const DELETE_USER_OR_AGENT = 45;
    public const RE_COM = 37;
    // TODO: 数据多语言
    private static $eventMsg = [
        11 => 'logger.agent,%s,logger.loginSucces',
        12 => 'logger.user,%s,logger.content2,：%.3f',
        13 => '%s,logger.content3!',
        21 => 'logger.content4,%s,logger.content5',
        22 => 'logger.content6,%s,logger.content5',
        23 => 'logger.content4,%s,logger.content7',
        24 => 'logger.content6,%s,logger.content7',
        34 => 'logger.user,%s,logger.content20,%s,logger.content19,%.3f,logger.content13,%.3f',
        35 => 'logger.user,%s,logger.content20,%s,logger.content21,%.3f,logger.content13,%.3f',
        36 => 'logger.user,%s,logger.content20,%s,logger.content31,%.3f,logger.content13,%.3f',
        37 => 'logger.user,%s,logger.content20,%s,logger.content21,%.3f,logger.content13,%.3f',
    ];

    /**
     * @param User $user
     * @param array $data
     * @return mixed|SysLog
     */
    public function save(User $user,$data)
    {
        $sysLog = new SysLog();
        $sysLog->type = $data['type'];
        $sysLog->obj_id = $user->id;
        $sysLog->username = $user->sn;
        $sysLog->operator = 'play_api';
        $event_group = (int)substr((string)$data['event'], 0, 1);
        $sysLog->event_group = $event_group;
        $sysLog->event = $data['event'];

        $sysLog->change_point = $data['change_point'] ?? 0;
        $sysLog->point = $user->point;

        $sysLog->operate_content = $this->buildMsg($data['event'], $data['msg_data']);
        $sysLog->remark = $data['remark'] ?? '';
        $sysLog->ip = $data['ip'];
//        $location = geoip($data['ip'])->toArray();
        $sysLog->country ='US';
        $sysLog->state = 'CT';
        $sysLog->save();
        return $sysLog;
    }


    /**
     * @param $event
     * @param $data
     * @return string
     */
    private function buildMsg($event, $data): string
    {
        return sprintf(self::$eventMsg[$event], ...$data);
    }

}
