<?php
/**
 * Copyright 2024 Huawei Technologies Co.,Ltd.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache LICENSE, Version 2.0 (the
 * "LICENSE"); you may not use this file except in compliance
 * with the LICENSE.  You may obtain a copy of the LICENSE at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the LICENSE is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the LICENSE for the
 * specific language governing permissions and limitations
 * under the LICENSE.
 */

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