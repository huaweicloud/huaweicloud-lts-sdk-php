<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

class LogMessage
{

    public $groupId;

    public $streamId;

    public $projectId;

    public $logGroup;


    public function __construct(string $groupId, string $streamId, string $projectId, LogGroup $logGroup)
    {
        $this->groupId = $groupId;
        $this->streamId = $streamId;
        $this->projectId = $projectId;
        $this->logGroup = $logGroup;
    }

}