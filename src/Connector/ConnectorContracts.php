<?php


namespace JasonYH\SwooleQueue\Connector;

/**
 * 驱动层需要实现的接口约定
 * Interface ConnectorContracts
 * @author : JasonYH <robotme@hotmail.com>
 * @package JasonYH\SwooleQueue\Connector
 */
interface ConnectorContracts
{

    /**
     * 获取队列数量
     * @param $queueName
     * @return mixed
     */
    function queueLen($queueName);
    /**
     * 推送一个任务到队列，并返回任务id
     * @param string $queueName     队列名
     * @param string$job            任务类
     * @param array $param   任务参数
     * @param int $delay            延时时间
     * @param int $ttr              任务超时时间
     * @return string               任务id
     */
    function push (string $queueName,$job,array $param,int $delay=0,int $ttr=60):string ;


    /**
     * 返回一个任务类
     * @param string $queueName 任务名
     * @return mixed 返回任务
     */
    function pop(string $queueName) ;


    /**
     * 返回当前连接驱动实例
     * @return mixed
     */
    function getConnector();

}