<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

use HuaweiCloud\SDK\LTS\UTIL\Common;

class Log
{

    public $time = 0;

    // LogConent list
    public $contents = [];

    // string
    public $labels;

    /**
     * @param array $contents
     * @param $labels
     */
    public function __construct(array $contents, $labels)
    {
        $this->contents = $contents;
        $this->labels = $labels;
    }

    public function Size(): int
    {
        $l = 0;
        $n = 0;
        $n += 1 + Common::SovLog($this->time);
        if ($this->contents == null || count($this->contents) <= 0) {
            return $n;
        }

        foreach ($this->contents as $index => $value) {
            $l = $value == null ? 0 : $value->Size();
            $n += 1 + $l + Common::SovLog($l);
        }
        return $n;
    }
}