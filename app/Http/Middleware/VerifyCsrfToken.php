<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/pp/authenticate','/pp/balance','/pp/bet','/pp/result','/pp/refund','/pp/bonusWin','/pp/jackpotWin','/pp/promoWin','/pp/endround','getGameResult','/hb/user','/hb/bet'
        ,'/pg/user','/pg/bet','/pg/balance' ,'/afb/result','/afb/bet','/afb/balance','/afb/refund'
    ];
}
