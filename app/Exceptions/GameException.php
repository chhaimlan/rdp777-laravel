<?php
/**
 * Created by PhpStorm.
 * User: Young<YoungShen@fih-foxconn.com.cn
 * Date: 2019/4/22
 * Time: 8:50
 */

namespace App\Exceptions;
use Illuminate\Http\Response;

class GameException extends \RuntimeException
{
    public static $error = [
        99 => 'UnKnow error',
        100=>'Account not exist',
        101=>'Account locked or blocked',
        102=>'Secret key incorrect',
        103=>'data signature error',
        104=>'The request header error', // header缺少x-agent
        105=>'Agent not exist',
        106=>'Request data error', //请求数据错误
        107=>'Server busy. Try again later.',
        108=>'The Agent is locked or blocked',
        109=>'Username is registered',
        110=>'User not online',
        // 下面的还没用到
        111=>'Query time range out of limitation',
        112=>'API recently called',
        113=>'Username duplicated',
        114=>'Currency not exist',
        116=>'Username does not exist',
        120=>'Amount must greater than zero',
        121=>'Not enough points to credit/debit/bet',
        122=>'Order ID already exists',
        125=>'Kick user fail',
        127=>'Invalid order ID format',
        128=>'Decryption error',
        129=>'System under maintenance',
        130=>'User account is locked (disabled)',
        132=>'Sign unmatch',
        133=>'Create user failed',
        134=>'Game code not found',
        135=>'Game access denied',
        136=>'Not enough point to bet',
        137=>'Bet string error',
        138=>'Bet time ended or not started',
        142=>'Parameter(s) error',
        144=>'Query type invalid',
        145=>'Parameter decimal point greater than 2',
        146=>'API access denied',
        147=>'BetLimit does not existed',
        148=>'MaxBalance not zero or smaller than user balance',
        149=>'Input amount under minimum value',
        150=>'Function has been deprecated',
        151=>'Duplicate login',
    ];
    public function __construct($code = 10000,$message = null)
    {
        if(empty($message)){
            if(!isset(self::$error[$code])){
                $code = 100;
            }
            $message = self::$error[$code];
        }
        parent::__construct($message,$code);
    }

    public function render(){
        return response()->json([
            'code' => $this->code,
            'msg'  => $this->message
        ], Response::HTTP_OK, [], 0);
    }
}
