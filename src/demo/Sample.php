<?php


use HuaweiCloud\SDK\LTS\MODEL\Config;
use HuaweiCloud\SDK\LTS\MODEL\LogMessage;
use HuaweiCloud\SDK\LTS\MODEL\LTS_Store;
use function HuaweiCloud\SDK\LTS\Consumer\consumer;
use const HuaweiCloud\SDK\LTS\Consumer\MSG_TYPE;

include '../consumer/Consumer.php';
require "../../vendor/autoload.php";

$endpoint = "";
$ak = "";
$sk = "";
$projectId = "";
$region = "";
$logGroup = "";
$logStream = "";


$sendLogCount = 30000;
$logContext =  "";

$config = Config::getDefaultConfig($ak, $sk, $endpoint, $region, $projectId);

# 这里需要启动一个子线程，子线程用来处理从主线程发送过来的日志
$pid = pcntl_fork();
if ($pid == -1) {
    // 创建失败
    exit("fork progress error!\n");
} else if ($pid == 0) {
    // 启动子进程进行日志消费，用来处理主进程推送过来的日志
    $child_pid = posix_getpid();
    var_dump("child processor $child_pid will consumer parent send log", $pid);
    consumer($config);
    exit("({$child_pid})child progress end!\n");
} else {
    global $msgQueue;
    var_dump("parent processor $pid will send log", $pid);

    for ($i = 0; $i < $sendLogCount; $i++) {
        $log = array($logContext);
        $label = array("A" => "B");
        $log = LTS_Store::GenerateLogGroup($log, $label);
        $logmsg = new LogMessage($logGroup, $logStream, $projectId, $log);
        msg_send($msgQueue, MSG_TYPE, $logmsg, true, false);
    }
    sleep(50000);
}



