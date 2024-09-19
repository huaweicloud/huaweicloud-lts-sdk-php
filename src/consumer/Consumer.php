<?php

namespace HuaweiCloud\SDK\LTS\Consumer;


use HuaweiCloud\SDK\LTS\HTTP\Client;
use HuaweiCloud\SDK\LTS\MODEL\Batch;
use HuaweiCloud\SDK\LTS\MODEL\Config;
use HuaweiCloud\SDK\LTS\MODEL\LogMessage;
use HuaweiCloud\SDK\LTS\UTIL\Common;


// 创建消息队列,以及定义消息类型(类似于数据库中的库)
$id = ftok(__FILE__, 'm');
$msgQueue = msg_get_queue($id);
const MSG_TYPE = 1;
const DELETER = "-";

// key, Batch
$logData = array();
$logCount = 0;

// 消费者，用来获取数据
function consumer(Config $config)
{
    global $msgQueue;
    global $logData;
    global $logCount;
    $pid = posix_getpid();
    var_dump("in consumer function, pid is", $pid);

    $endpoint = $config->endpoint;
    $ak = $config->ak;
    $sk = $config->sk;
    $region = $config->region;

    $ltsClient = new Client($endpoint, $config->httpTimeOut, $config->maxRetryTimes);

    while (true) {
        // 挨个判断是否满足发送条件（大小大于 0.5M，时间超过 2000ms）
        foreach ($logData as $key => $valueBatch) {
            if ($valueBatch->logSize >= $config->batchSizeThreshold) {
                // todo
                var_dump("log is full, this time send log size is, log count is", $valueBatch->logSize, $valueBatch->logCount);
                $ltsClient->doLtsHttpRequest($endpoint, $valueBatch->projectId, $valueBatch->groupId, $valueBatch->streamId, json_encode($valueBatch->logGroup), $ak, $sk, $region);
                $logCount += $valueBatch->logCount;
                var_dump("====== already send log count", $logCount);
                unset($logData[$key]);

            } else if (Common::Microtime_float() - $valueBatch->createTime >= $config->lingerMs) {
                // todo
                var_dump("time is end, this time send log size is, log count is", $valueBatch->logSize, $valueBatch->logCount);
                $ltsClient->doLtsHttpRequest($endpoint, $valueBatch->projectId, $valueBatch->groupId, $valueBatch->streamId, json_encode($valueBatch->logGroup), $ak, $sk, $region);
                $logCount += $valueBatch->logCount;
                var_dump("====== already send log count", $logCount);
                unset($logData[$key]);
            }
        }

        while (msg_receive($msgQueue, MSG_TYPE, $msgType, 13421772, $message, true, MSG_IPC_NOWAIT)) {
            $key = getKey($message->groupId, $message->streamId);
            if (!array_key_exists($key, $logData)) {
                $batch = Batch::createNewBatch($message->projectId, $message->groupId, $message->streamId);
                $batch->addLog($message->logGroup);
                $logData[$key] = $batch;
            } else {
                $batch = $logData[$key];
                $batch->addLog($message->logGroup);
                $logData[$key] = $batch;
            }
            if (isSizeExceed($message, $logData, $config)) {
                break;
            }
        }
    }
}

function isSizeExceed(LogMessage $message, array $logData, Config $config): bool
{
    $key = getKey($message->groupId, $message->streamId);
    if (!array_key_exists($key, $logData)) {
        return false;
    }
    return $logData[$key]->logSize >= $config->batchSizeThreshold;
}

function getKey(string $groupId, string $streamId)
{
    return $groupId . DELETER . $streamId . DELETER;
}


