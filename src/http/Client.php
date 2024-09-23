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

namespace HuaweiCloud\SDK\LTS\HTTP;

use GuzzleHttp\Exception\GuzzleException;
use HuaweiCloud\SDK\LTS\Auth\Signer;


define("UPLOAD_URL", "/v2/internal/%s/lts/groups/%s/streams/%s/tenant/batch-contents");

class Client
{
    private $httpClient;


    public function __construct($endpoint, $httpTimeOut, $maxRetry)
    {
        $this->httpClient = new HttpClient($endpoint, $httpTimeOut, $maxRetry);
    }


    /**
     * @throws GuzzleException
     */
    public function doLtsHttpRequest($endpoint,
                                     $projectId,
                                     $logGroup,
                                     $logStream,
                                     $body,
                                     $ak,
                                     $sk,
                                     $region)
    {
        $url = sprintf(UPLOAD_URL, $projectId, $logGroup, $logStream);
        $sdkRequest = new LTSRequest("POST", $endpoint, $url, array(), $body, $region);
        $signer = new Signer($ak, $sk);
        $sdkRequest = $signer->Sign($sdkRequest);
        $this->httpClient->doRequestSync($sdkRequest);
    }

}
