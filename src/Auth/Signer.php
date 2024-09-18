<?php

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


    /**
     * @param string $Key
     */
    public function setKey($Key)
    {
        $this->Key = $Key;
    }

    /**
     * @param string $Secret
     */
    public function setSecret($Secret)
    {
        $this->Secret = $Secret;
    }

    private function findHeader($request, $header)
    {
        foreach ($request->headerParams as $key => $value) {
            if (!strcasecmp($key, $header)) {
                return $value;
            }
        }

        return null;
    }

    private function CanonicalRequest($request, $signedHeaders)
    {
        $CanonicalURI = $this->CanonicalURI($request);
        $CanonicalQueryString = $this->CanonicalQueryString($request);
        $canonicalHeaders = $this->CanonicalHeaders($request, $signedHeaders);
        $signedHeadersString = join(';', $signedHeaders);
        $hash = $this->findHeader($request, HeaderContentSha256);
        if (!$hash) {
            $hash = hash('sha256', $request->body);
        }

        return "$request->method\n$CanonicalURI\n$CanonicalQueryString\n$canonicalHeaders\n$signedHeadersString\n$hash";
    }

    // CanonicalURI returns request uri
    private function CanonicalURI($r)
    {
        $pattens = explode('/', $r->url);
        $uri = [];
        foreach ($pattens as $v) {
            array_push($uri, str_replace(['+', '%7E'], ['%20', '~'], urlencode(($v))));
        }
        $urlpath = join('/', $uri);
        if ('/' != substr($urlpath, -1)) {
            $urlpath = $urlpath . '/';
        }

        return $urlpath;
    }

    // CanonicalQueryString
    private function CanonicalQueryString($r)
    {
        $keys = [];
        foreach ($r->queryParams as $key => $value) {
            array_push($keys, $key);
        }
        sort($keys);
        $a = [];
        foreach ($keys as $key) {
            $k = str_replace(['+', '%7E'], ['%20', '~'], urlencode($key));
            $value = $r->queryParams[$key];
            if (is_array($value)) {
                sort($value);
                foreach ($value as $v) {
                    $kv = "$k=" . str_replace(['+', '%7E'], ['%20',
                            '~',], urlencode($v));
                    array_push($a, $kv);
                }
            } else {
                if (!is_string($value)) {
                    $value = json_encode($value);
                }
                $kv = "$k=" . str_replace(['+', '%7E'], ['%20',
                        '~',], urlencode($value));
                array_push($a, $kv);
            }
        }

        return join('&', $a);
    }

    // CanonicalHeaders
    private function CanonicalHeaders($r, $signedHeaders)
    {
        $headers = [];
        foreach ($r->headerParams as $key => $value) {
            $headers[strtolower($key)] = trim($value);
        }
        $a = [];
        foreach ($signedHeaders as $key) {
            array_push($a, $key . ':' . $headers[$key]);
        }

        return join("\n", $a) . "\n";
    }

    // SignedHeaders
    private function SignedHeaders($r)
    {
        $a = [];
        foreach ($r->headerParams as $key => $value) {
            array_push($a, strtolower($key));
        }
        sort($a);

        return $a;
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
        $date = $this->findHeader($request, HeaderXDate);
        if ($date) {
            $t = date_create_from_format(BasicDateFormat, $request->headerParams[HeaderXDate], timezone_open("UTC"));
        }
        if (empty($t)) {
            $t = date_create(null, timezone_open("UTC"));
            $request->headerParams[HeaderXDate] = date_format($t, BasicDateFormat);
        }
        $queryString = $this->CanonicalQueryString($request);
        if ('' != $queryString) {
            $queryString = '?' . $queryString;
        }

        $request->headerParams = array_merge(array("Content-Type" => "application/json"), $request->headerParams);
        $signedHeaders = $this->SignedHeaders($request);
        $canonicalRequest = $this->CanonicalRequest($request, $signedHeaders);
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
