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