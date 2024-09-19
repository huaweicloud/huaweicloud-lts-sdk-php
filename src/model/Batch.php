<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

use HuaweiCloud\SDK\LTS\UTIL\Common;

/**
 * 一个批次表示一个日志流的一批日志
 */
class Batch
{

    // array(LogGroup)
    public $logGroup;

    public $projectId;

    public $groupId;

    public $streamId;

    /**
     * @logSize int 该batch中日志的大小
     */
    public $logSize;

    /**
     * @logCount int 该batch中日志的数量
     */
    public $logCount;

    /**
     * @createTime 该Batch创建的时间，用来判断batch中数据是否保留过长时间
     */
    public $createTime;



    public function __construct()
    {
        $this->logGroup = new LogGroup(array());
        $this->logSize = 0;
        $this->logCount = 0;
        $this->createTime = Common::Microtime_float();
    }


    public static function createNewBatch(string $projectId, string $groupId, string  $streamId): Batch
    {
        $batch = new Batch();
        $batch->projectId = $projectId;
        $batch->groupId = $groupId;
        $batch->streamId = $streamId;
        return $batch;
    }


    public function addLog(LogGroup $logData) {
        if ($this->logGroup == null) {
            $this->logGroup =new LogGroup(array());
        }

        foreach ($logData->log_items as $index=>$log) {
            $this->logGroup->log_items[] = $log;
        }
        $this->logCount += count($logData->log_items);
        $this->logSize += $logData->Size();
    }
}