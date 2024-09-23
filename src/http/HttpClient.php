<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache LICENSE, Version 2.0 (the
 * "LICENSE"); you may not use this file except in compliance
 * with the LICENSE.  You may obtain a copy of the LICENSE at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the LICENSE is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the LICENSE for the
 * specific language governing permissions and limitations
 * under the LICENSE.
 */

namespace HuaweiCloud\SDK\LTS\HTTP;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HuaweiCloud\SDK\LTS\UTIL\Common;


class HttpClient
{
    protected $client;


    public function __construct($endpoint, $httpTimeOut, $maxRetry)
    {
        // 创建 Handler
        $handlerStack = HandlerStack::create(new CurlHandler());
        // 创建重试中间件，指定决策者为 $this->retryDecider(),指定重试延迟为 $this->retryDelay()
        $handlerStack->push(Middleware::retry($this->retryDecider($maxRetry), $this->retryDelay()));

        $this->client = new \GuzzleHttp\Client(['base_uri' => $endpoint,
            'verify' => false,
            'timeout' => $httpTimeOut,
            'handler' => $handlerStack]);
    }

    /**
     * retryDecider
     * 返回一个匿名函数, 匿名函数若返回false 表示不重试，反之则表示继续重试
     * @return \Closure
     */
    protected function retryDecider(int $maxRetryTime): \Closure
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) use ($maxRetryTime) {
            // 超过最大重试次数，不再重试
            if ($retries > 0) {
                var_dump("retry count is", $retries);
            }
            if ($retries >= $maxRetryTime) {
                return false;
            }

            // 请求失败，继续重试
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // 如果请求有响应，但是状态码大于等于500，继续重试(这里根据自己的业务而定)
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
     * @return \Closure
     */
    protected function retryDelay(): \Closure
    {
        return function ($numberOfRetries) {
            if ($numberOfRetries > 0) {
                var_dump("retry count is", $numberOfRetries);
            }
            return 1000 * pow(2, $numberOfRetries);
        };
    }


    /**
     * @throws GuzzleException
     */
    public function doRequestSync(LTSRequest $sdkRequest)
    {
        $request = new Request($sdkRequest->method,
            $sdkRequest->url,
            $sdkRequest->headerParams,
            $sdkRequest->body);

        $startTime = Common::Microtime_float();
        $request  = $this->client->send($request);
        var_dump("send log to lts response is", $request->getStatusCode());
        $endTime = Common::Microtime_float();
        var_dump("send log to lts cost", $endTime - $startTime);
    }
}
