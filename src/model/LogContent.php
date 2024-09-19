<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

use HuaweiCloud\SDK\LTS\UTIL\Common;

class LogContent
{
    public $log_time_ns;     // long


    public $log;     // string

    /**
     * @param $log_time_ns
     * @param $log
     */
    public function __construct($log_time_ns, $log)
    {
        $this->log_time_ns = $log_time_ns;
        $this->log = $log;
    }

    public function Size(): int
    {
        $logLength = 0;
        if ($this->log != null) {
            $logLength = strlen($this->log);
        }

        return $logLength + Common::SovLog($this->log_time_ns);
    }
}