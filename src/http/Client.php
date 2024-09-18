<?php

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
