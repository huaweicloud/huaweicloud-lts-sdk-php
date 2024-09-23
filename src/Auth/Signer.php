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

namespace HuaweiCloud\SDK\LTS\Auth;

use HuaweiCloud\SDK\LTS\HTTP\LTSRequest;

define('BasicDateFormat', "Ymd\THis\Z");
define('DerivedDateFormat', "Ymd");
define('Algorithm', 'SDK-HMAC-SHA256');
define('HeaderXDate', 'X-Sdk-Date');
define('HeaderHost', 'host');
define('HeaderAuthorization', 'Authorization');
define('HeaderContentSha256', 'X-Sdk-Content-Sha256');
define('AuthServiceName', 'lts');
define('DerivationAlgorithm', 'V11-HMAC-SHA256');

class Signer
{
    private $Key = '';
    private $Secret = '';

    /**
     * @param string $Key
     * @param string $Secret
     */
    public function __construct(string $Key, string $Secret)
    {
        $this->Key = $Key;
        $this->Secret = $Secret;
    }

    private function searchHeader($requestParam, $requestHeader)
    {
        foreach ($requestParam->headerParams as $key => $value) {
            if (!strcasecmp($key, $requestHeader)) {
                return $value;
            }
        }

        return null;
    }

    private function LtsCanonicalHttpRequest($request, $signedLtsHeaders)
    {
        $CanonicalLtsURI = $this->CanonicalLtsURI($request);
        $CanonicalLtsQueryString = $this->CanonicalLtsQueryString($request);
        $canonicalLtsHeaders = $this->CanonicalLtsHeaders($request, $signedLtsHeaders);
        $signedLtsHeadersString = join(';', $signedLtsHeaders);
        $hash = $this->searchHeader($request, HeaderContentSha256);
        if (!$hash) {
            $hash = hash('sha256', $request->body);
        }

        return "$request->method\n$CanonicalLtsURI\n$CanonicalLtsQueryString\n$canonicalLtsHeaders\n$signedLtsHeadersString\n$hash";
    }

    // CanonicalURI returns request uri
    private function CanonicalLtsURI($r)
    {
        $urlPattens = explode('/', $r->url);
        $uri = [];
        foreach ($urlPattens as $value) {
            array_push($uri, str_replace(['+', '%7E'], ['%20', '~'], urlencode(($value))));
        }
        $ltsUrlpath = join('/', $uri);
        if ('/' != substr($ltsUrlpath, -1)) {
            $ltsUrlpath = $ltsUrlpath . '/';
        }

        return $ltsUrlpath;
    }

    // CanonicalQueryString
    private function CanonicalLtsQueryString($ltsQuery)
    {
        $queryKeys = [];
        foreach ($ltsQuery->queryParams as $index => $queryValue) {
            array_push($queryKeys, $index);
        }
        sort($queryKeys);
        $ltsArray = [];
        foreach ($queryKeys as $queryKey) {
            $k = str_replace(['+', '%7E'], ['%20', '~'], urlencode($queryKey));
            $queryValue = $ltsQuery->queryParams[$queryKey];
            if (is_array($queryValue)) {
                sort($queryValue);
                foreach ($queryValue as $item) {
                    $keyValue = "$k=" . str_replace(['+', '%7E'], ['%20',
                            '~',], urlencode($item));
                    array_push($ltsArray, $keyValue);
                }
            } else {
                if (!is_string($queryValue)) {
                    $queryValue = json_encode($queryValue);
                }
                $keyValue = "$k=" . str_replace(['+', '%7E'], ['%20',
                        '~',], urlencode($queryValue));
                array_push($ltsArray, $keyValue);
            }
        }

        return join('&', $ltsArray);
    }

    // CanonicalHeaders
    private function CanonicalLtsHeaders($r, $signedHeaders)
    {
        $ltsHeaders = [];
        foreach ($r->headerParams as $index => $value) {
            $ltsHeaders[strtolower($index)] = trim($value);
        }
        $a = [];
        foreach ($signedHeaders as $signedHeader) {
            array_push($a, $signedHeader . ':' . $ltsHeaders[$signedHeader]);
        }

        return join("\n", $a) . "\n";
    }

    // SignedHeaders
    private function SignedHeaders($r)
    {
        $ltsArray = [];
        foreach ($r->headerParams as $index => $value) {
            array_push($ltsArray, strtolower($index));
        }
        sort($ltsArray);

        return $ltsArray;
    }


    // Create the HWS Signature.
    private function SignStringToSign($stringToSign, $signingKey)
    {
        return hash_hmac('sha256', $stringToSign, $signingKey);
    }


    private function findMethod($request)
    {
        return $request->method;

    }

    private function StringToSignDerived($canonicalRequest, $info, $t)
    {
        $hexencode = hash('sha256', $canonicalRequest);
        $shortTime = date_format($t, DerivedDateFormat);
        return DerivationAlgorithm . "\n$shortTime\n$info\n$hexencode";

    }

    private function DerivationAuthHeaderValue($signature, $ak, $info, $signedHeaders)
    {
        $signedHeadersString = join(';', $signedHeaders);
        return DerivationAlgorithm . " Credential=$ak/$info, SignedHeaders=$signedHeadersString, Signature=$signature";
    }

    public function Sign(LTSRequest $request)
    {
        $method = $this->findMethod($request);
        if ($method != "POST" && $method != "PATCH" && $method != "PUT") {
            $request->Body = "";
        }
        $date = $this->searchHeader($request, HeaderXDate);
        if ($date) {
            $t = date_create_from_format(BasicDateFormat, $request->headerParams[HeaderXDate], timezone_open("UTC"));
        }
        if (empty($t)) {
            $t = date_create(null, timezone_open("UTC"));
            $request->headerParams[HeaderXDate] = date_format($t, BasicDateFormat);
        }
        $queryString = $this->CanonicalLtsQueryString($request);
        if ('' != $queryString) {
            $queryString = '?' . $queryString;
        }

        $request->headerParams = array_merge(array("Content-Type" => "application/json"), $request->headerParams);
        $signedHeaders = $this->SignedHeaders($request);
        $canonicalRequest = $this->LtsCanonicalHttpRequest($request, $signedHeaders);
        $info = date_format($t, DerivedDateFormat) . "/" . $request->region . "/" . AuthServiceName;
        $stringToSign = $this->StringToSignDerived($canonicalRequest, $info, $t);
        $derivationKey = hash_hkdf("sha256", $this->Secret, 32, $info, $this->Key);
        $derivationKey = $this->hexToStr($derivationKey);
        $signature = $this->SignStringToSign($stringToSign, $derivationKey);
        $authValue = $this->DerivationAuthHeaderValue($signature, $this->Key, $info, $signedHeaders);
        $request->headerParams[HeaderAuthorization] = $authValue;
        return $request;
    }


    function hexToStr($hex)
    {
        $num = strlen($hex) * 2;
        $charArray = "";
        $num2 = 0;
        for ($i = 0; $i < $num; $i += 2) {
            $b = $hex[$num2++];
            $b = ord($b);
            $charArray .= $this->getHexValue($b / 16).$this->getHexValue($b % 16);
        }

        return $charArray;
    }

    function getHexValue($i): string
    {
        if ($i < 10)
        {
            return chr($i + 48);
        }

        return chr($i - 10 + 97);
    }

}
