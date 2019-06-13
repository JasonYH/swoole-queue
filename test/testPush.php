<?php

include_once '../vendor/autoload.php';

$a = new \JasonYH\SwooleQueue\Connector\Redis();
$qname = 'T';
echo $a->push('T','q1',[],0,20);
echo $a->push('T','q2',[],0,5);
echo "\n";

var_dump($a->queueLen('T'));







