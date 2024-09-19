<?php

namespace HuaweiCloud\SDK\LTS\MODEL;

use HuaweiCloud\SDK\LTS\UTIL\Common;

class LogGroup
{

    public $log_items = [];

    public $Category;

    public $LogTags = [];


    public function isEmptyLogs(): bool
    {
        return $this->log_items == null || count($this->log_items) == 0;
    }


    /**
     * @param array $log_items
     * @param $Category
     * @param array $LogTags
     */
    public function __construct(array $log_items)
    {
        $this->log_items = $log_items;
    }

    public function Size(): int
    {
        $l = 0;
        $n = 0;
        if ($this->log_items != 0 && count($this->log_items) > 0) {
            foreach ($this->log_items as $index => $value) {
                $l = $value->Size();
                $n += 1 + $l + Common::SovLog($l);
            }
        }

        if ($this->Category != null) {
            $l = strlen($this->Category);
            $n += 1 + $l + Common::SovLog($l);
        }

        if ($this->LogTags != 0 && count($this->LogTags) > 0) {
            foreach ($this->LogTags as $index => $value) {
                $l = $value->Size();
                $n += 1 + $l + Common::SovLog($l);
            }
        }

        return $n;
    }

}

class LogTag {
    public $Key;

    public $Value;

    public function Size(): int
    {
        $l = 0;
        $n = 0;
        if ($this->Key != null) {
            $l = strlen($this->Key);
            $n += 1 + $l + Common::SovLog($l);
        }
        if ($this->Value != null) {
            $l = strlen($this->Value);
            $n += 1 + $l + Common::SovLog($l);
        }
        return $n;
    }
}