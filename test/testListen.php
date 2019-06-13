<?php
include_once '../vendor/autoload.php';
$a = new \JasonYH\SwooleQueue\Connector\Redis();


while (true) {
    $job = $a->pop('T');
    if (!empty($job)) {
        echo date('H:i:s',time()).':  '. $job . "\n";
        continue;
    }
    usleep(2000);
}