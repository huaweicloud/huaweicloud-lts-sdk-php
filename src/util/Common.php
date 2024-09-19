<?php

namespace HuaweiCloud\SDK\LTS\UTIL;

class Common
{
    public static function SovLog($x): int
    {
        $n = 0;
        while (true) {
            $n++;
            $x >>= 7;
            if ($x == 0) {
                break;
            }
        }
        return $n;
    }

    public static function Microtime_float(): int
    {
        list($usec, $sec) = explode(" ", microtime());
        $result = ((float)$usec + (float)$sec) * 1000000;
        return (int)$result / 1000;
    }
}

