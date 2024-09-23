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

class Config
{

    public $httpTimeOut;
    public $lingerMs;

    public $maxRetryTimes;
    public $batchSizeThreshold;

    public $ak;

    public $sk;

    public $endpoint;

    public $region;

    public $projectId;

    public static function getDefaultConfig(string $ak, string $sk, string $endpint, string $region, string $projectId): Config
    {
        $config = new Config();
        $config->httpTimeOut = 60;
        $config->lingerMs = 2000;
        $config->batchSizeThreshold = 512 * 1024;
        $config->maxRetryTimes = 3;
        $config->ak = $ak;
        $config->sk = $sk;
        $config->endpoint = $endpint;
        $config->region = $region;
        $config->projectId = $projectId;
        return $config;
    }
}