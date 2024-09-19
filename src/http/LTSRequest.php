<?php

namespace HuaweiCloud\SDK\LTS\HTTP;

class LTSRequest
{

    public $method = '';

    public $url = '';

    public $headerParams = [];
    public $body = '';

    public $region = '';

    public $queryParams = [];

    public $endpoint = '';

    public function __construct($method = null,
                                $endpoint = null,
                                $url = null,
                                $headerParams = [],
                                $body = null,
                                $region = null)
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->url = $url;
        $this->headerParams = $headerParams;
        $this->body = $body;
        $this->region = $region;
    }

}