<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

use HuaweiCloud\SDK\LTS\UTIL\Common;

class LTS_Store
{


    static function GenerateLog(array $logList, array $labels): Log
    {
        $content = array();
        foreach ($logList as $index => $value) {
           $content[] = new LogContent(Common::microtime_float(), $value);
        }
        $labelStr = json_encode($labels);
        return new Log($content, $labelStr);
    }

    static function GenerateLogGroup(array $logList, array $labels): LogGroup
    {
        $log = self::GenerateLog($logList, $labels);
        $log_items = array();
        $log_items[] = $log;
        return new LogGroup($log_items);
    }

}

