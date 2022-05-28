<?php


namespace App\Logging;

use Illuminate\Support\Env;
use Monolog\Handler\RedisHandler;
use Monolog\Logger;

class GameRedisLogger
{
    /**
     * 创建一个 Monolog 实例。
     *
     * @param array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {

        $redisClient = new \Redis();
        $redisConfig = $config['connect'];
        $redisClient->connect($redisConfig['host'],$redisConfig['port']);
        $redisClient->auth($redisConfig['password']);
        $redisClient->select($redisConfig['database']);
//        $redisClient = Redis::connection('redis.log')->client();
        $handler = new RedisHandler( // 创建 Handler
            $redisClient,
            $config['key'],
            $config['level']
        );

        $handler->setLevel($config['level']);
        $logger = new Logger(Env::get('APP_ENV'));
        $logger->pushHandler($handler); // 挂载 Handler
//        $logger->pushProcessor(new WebProcessor($_SERVER)); // 记录额外的请求信息


        return $logger;
    }
}
