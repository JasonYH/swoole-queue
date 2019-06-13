<?php


namespace JasonYH\SwooleQueue\Connector;


/**
 * 驱动层公共抽象类
 * Class Connector
 * @package JasonYH\SwooleQueue\Connector
 */
abstract class Connector implements ConnectorContracts
{
    protected $options = [];
    /**
     * 创建任务载荷
     * @param $job
     * @param array $param
     * @param int $ttr
     * @return string
     */
    protected function createPayload($job, array $param,int $ttr):string
    {
        $payload ['job'] = $job;
        $payload ['param'] = $param;
        $payload ['id'] = $this->createRandomId();
        $payload['ttr'] = $ttr;
        $payload ['attempts'] = 1;
        return json_encode($payload);
    }
    /**
     * 生产唯一id
     * @return string
     */
    private  function createRandomId():string
    {
        $length = 16;
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()+-=';
        $md5 =  md5(mb_substr(str_shuffle(str_repeat($pool, $length)), 0, $length, 'UTF-8').uniqid().microtime(),'');
        return mb_substr($md5, 0, 16, 'UTF-8');
    }

    /**
     * 解析队列名称
     * @param string $queueName 队列名称
     * @param bool $delay 是否延时队列
     * @return string
     */
    protected function resolveName($queueName,bool $delay =false):string
    {
        $prefix = 'queues:';
        $suffix = $delay ? ':delay' : '';
        return $prefix.$queueName.$suffix;
    }
}