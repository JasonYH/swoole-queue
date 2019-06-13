<?php


namespace JasonYH\SwooleQueue\Job;

/**
 * 任务类需要实现的接口约定
 * Interface JobContracts
 * @author : JasonYH <robotme@hotmail.com>
 * @package JasonYH\SwooleQueue\Job
 */
interface JobContracts
{
    /**
     * 获取当前任务ID
     * @return string
     */
    function getJobId():string ;

    /**
     *  获取当前任务解码后的载荷
     * @return array
     */
    function getPayload():array;

    /**
     * 获取任务的原始载荷
     * @return string
     */
    function getRawBody(): string;

    /**
     * 获取Job处理类的类名
     * @return string
     */
    function getJobName():string;

    /**
     * 获取当前所在任务队列名
     * @return string
     */
    function getQueueName(): string;

    /**
     * 执行任务处理
     * @return mixed
     */
    function fire();

    /**
     * 重发任务
     * @param int $delay
     * @return mixed
     */
    function release(int $delay = 0);

    /**
     * 删除任务
     * @return mixed
     */
    function delete();

    /**
     * 确认任务是否有删除标记
     * @return bool
     */
    function isDeleted();

    /**
     * 确认任务是否有删除或重新发布标记
     * @return bool
     */
    function isDeletedOrReleased();

    /**
     * 执行Job的失败处理逻辑
     * @param  \Throwable $e
     * @return void
     */
    function failed(\Throwable $e);

}