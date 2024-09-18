<?php

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