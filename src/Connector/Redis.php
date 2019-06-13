<?php


namespace JasonYH\SwooleQueue\Connector;


class Redis extends Connector
{
    /** @var \Redis */
    protected $instance = null;

    protected $options = [
        'host' => 'redis',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 5,
        'attempts' => 5,
    ];

    /**
     * 初始化Redis 连接
     * @param array $options Redis 连接配置
     */
    function __construct(array $options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 获取连接
     */
    protected function connect()
    {
        if (is_null($this->instance)) {
            $redis = new \Redis();
            if ($this->options['select'] != 0) {
                $redis->select($this->options['select']);
            }
            if ($this->options['password'] != '') {
                $redis->auth($this->options['password']);
            }
            $redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            $this->instance = $redis;
        }

    }


    /**
     * 投递延时任务
     * @param string $queueName 队列名
     * @param string $payload 数据载荷
     * @param int $delay
     * @return string
     */
    private function pushDelay(string $queueName, string $payload, int $delay): string
    {
        $qname = $this->resolveName($queueName, true);
        $this->connect();
        $this->instance->zAdd($qname, [], time() + $delay, $payload);
        return json_decode($payload, true)['id'];
    }


    /**
     * 移除到期任务
     * @param string $from
     * @param $time
     * @return int
     */
    private function removeExpiredJobs(string $from, $time)
    {
        $this->connect();
        return $this->instance->zRemRangeByScore($from, '-inf', $time);

    }


    /**
     * 重新发布到期任务
     * @param $to
     * @param $jobs
     * @param bool $attmept
     * @return bool
     */
    private function pushExpiredJob($to, $jobs, $attmept = true)
    {
        if (!is_array($jobs)) {
            return false;
        }
        foreach ($jobs as &$job) {
            if ($attmept) {
                $item = json_decode($job, true);
                if ($item['attempts'] < $this->options['attempts']) {
                    $item['attempts'] = $item['attempts'] + 1;
                    $job = json_encode($item);
                    $this->instance->rPush($to, $job);
                }
            } else {
                $this->instance->rPush($to, $job);
            }

        }


    }

    /**
     * 合并到期任务
     * @param string $from
     * @param string $to
     * @param bool $attempt
     */
    private function mergenDelayJob(string $from, string $to, bool $attempt = true)
    {
        $this->connect();
        $this->instance->watch($from);
        $jobs = $this->getExpiredJobs($from, $time = time());
        if (count($jobs) > 0) {
            $this->instance->multi();
            //移除到期任务
            $this->removeExpiredJobs($from, $time);
            //重新发布到队列
            $this->pushExpiredJob($to, $jobs, $attempt);
            $this->instance->exec();
        }
        $this->instance->unwatch();


    }

    /**
     * 获取全部到期任务
     * @param $frome
     * @param $time
     * @return array
     */
    private function getExpiredJobs($frome, $time)
    {
        $this->connect();
        return $this->instance->zRangeByScore($frome, '-inf', $time);
    }


    /**
     * 发布任务
     * @param string $queueName
     * @param string $job
     * @param array $param
     * @param int $delay
     * @param int $ttr
     * @return string
     */
    public function push(string $queueName, $job, array $param, int $delay = 0, int $ttr = 60): string
    {
        $this->connect();
        //创建载荷
        $payload = $this->createPayload($job, $param, $ttr);
        if ($delay != 0) {
            return $this->pushDelay($queueName, $payload, $delay);
        }
        $qname = $this->resolveName($queueName);
        $this->instance->rPush($qname, $payload);
        return json_decode($payload, true)['id'];
    }

    public function pop(string $queueName)
    {
        $this->connect();
        //检查延时任务
        $this->mergenDelayJob($this->resolveName($queueName, true), $this->resolveName($queueName), false);
        //检查超时任务
        $this->mergenDelayJob($this->resolveName($queueName) . ':reserved', $this->resolveName($queueName));
        //取出一个任务 放入到执行集合中


        /*$Job = $this->instance->blPop($this->resolveName($queueName), 10);

        if (!empty($Job) && !is_null($Job) && is_array($Job)) {

            if (json_decode($Job[1]) && !is_int(json_decode($Job[1]))) {

                $this->instance->zAdd($this->resolveName($queueName) . ':reserved', [], json_decode($Job[1], true)['ttr'] + time(), $Job[1]);
                return $Job[1];

            }


        }*/


        $Job = $this->instance->lPop($this->resolveName($queueName));
        if (!empty($Job) && !is_null($Job) && json_decode($Job) && !is_int(json_decode($Job))) {
            $this->instance->zAdd($this->resolveName($queueName) . ':reserved', [], json_decode($Job, true)['ttr'] + time(), $Job);
            return $Job;
        }
        return null;
    }

    /**
     * 获取连接实例
     * @return mixed|\Redis
     */
    public function getConnector()
    {
        $this->connect();
        return $this->instance;
    }

    /**
     * 获取队列数量
     * @param $queueName
     * @return mixed|void
     */
    function queueLen($queueName)
    {
        $this->connect();
        $len['ready']= $this->instance->lLen($this->resolveName($queueName));
        $len ['delay'] = $this->instance->zCard($this->resolveName($queueName,true));
        $len ['reserved'] = $this->instance->zCard($this->resolveName($queueName) . ":reserved");
        return $len;

    }


}