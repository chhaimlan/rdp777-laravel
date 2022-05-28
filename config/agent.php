<?php
/**
 * Created by PhpStorm.
 * User: Yonug<820355121@qq.com>
 * Date: 2020/3/6
 * Time: 9:39
 */


return [
    'agentSn' =>env('AGENT_SN', 'hjlh'),
    /**
     * 币种
     */
    'currency' => env('CURRENCY', 'USD'),
    /**
     * 调用游戏开放接口的地址
     */
    'apiUrl'=> env('API_URL', 'http://34.96.166.20:8100/api/'),
    'keyword' => env('API_KEYWORD', 'asdasd'),
    'userName' => env('AGENT_NAME', 'XJ001'),
    'agentId' => env('AGENT_ID', '151'),
    'rupt_agent' => env('RUPT_AGENT', 'ruptmanager'),
    'rupt_key' => env('RUPT_KEY', 'bc055202011170508BB5782SSDFHHJJ'),
    'rupt_id' => env('RUPT_ID', '431'),
    'jionUrl' => env('JION_URL', 'http://lg88-xj-play.sy/jionSend?reffername'),
    'referralLink' => env('APP_REF', 'http://54.251.83.21:8879/#/pages/register/register'),
    'lobbyUrl' => env('APP_LOBY', 'http://54.251.83.21:8879/#/pages/index/index'),
    'ppurl' => env('PP_URL', 'https://api.prerelease-env.biz/IntegrationService/v3/http/CasinoGameAPI/'),
    'hburl' => env('HB_URL', 'http://ws-test.insvr.com/jsonapi/'),
    'pg_ot' => env('PG_OT', '99ce2a97123ef0fbd13ac56d28d5ede2'),
    'pg_key' => env('PG_KEY', '4eb8f8eba59ce0104509972be3d94e5c'),
    'pg_url' => env('PG_URL', 'https://api.pg-bo.me/external-datagrabber/'),
    'pg_check' => env('PG_CHECK', 'https://public.pg-redirect.net/history/redirect.html?'),
    'afb_key' => env('AFB_KEY', 'e06dcae665043dffe06dcae665043dffe06dcae665043dffe06dcae665043dff49ecab12db6764de840797c2fce6545f6928e95e44ffc7df2bcd11e8da27aa49b20fea842fac741f'),
    'afb_url' => env('AFB_URL', 'https://test.khsport.net/'),
    ];
