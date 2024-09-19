## 1. 使用前提
- 要使用LTS PHP SDK ，您需要拥有云账号以及该账号对应的 Access Key（AK）和 Secret Access Key（SK）。 请在华为云控制台“我的凭证-访问密钥”页面上创建和查看您的 AK&SK 。更多信息请查看 [访问密钥](https://support.huaweicloud.com/usermanual-ca/zh-cn_topic_0046606340.html)  。
- 要使用LTS PHP SDK 上报日志 ，您需要确认已在 [华为云控制台](https://console.huaweicloud.com/console/?locale=zh-cn&region=cn-north-4#/home) 开通LTS服务
- LTS PHP SDK 适用于
    - PHP 7.2.5 及以上版本
    - Linux系统


## 2. php扩展以及包管理
LTS PHP SDK使用了以下php扩展，并且使用composer进行包管理

### php扩展
- php-json
- php-process
- php-cli
- php-common
- php-help


### composer配置
```json
{
  "name": "huaweicloud/lts-sdk-php-demo",
  "description": "HUAWEICLOUD LTS PHP SDK DEMO",
  "keywords": [
    "php",
    "sdk",
    "rest",
    "api",
    "lts",
    "demo"
  ],
  "version": "1.0.0",
  "type": "library",
  "license": "Apache-2.0",
  "authors": [
    {
      "name": "lts"
    }
  ],
  "minimum-stability": "dev",
  "require": {
    "php": ">=7.2.5",
    "guzzlehttp/guzzle": "6.3.1",
    "guzzlehttp/psr7": "1.4.2",
    "guzzlehttp/promises": "1.3.1",
    "psr/http-message": "1.0.1",
    "monolog/monolog": ">=2.4.0",
    "ext-json": "*",
    "ext-sysvmsg": "*"
  },
  "config": {
    "secure-http": false
  },
  "repositories": {
    "packagist": {
      "type": "composer",
      "url": "http://mirrors.tools.huawei.com/php/"
    }
  },
  "autoload": {
    "classmap": [
      "src/Auth",
      "src/demo",
      "src/http",
      "src/model",
      "src/util",
      "src/consumer"
    ]
  }
}
```


## 3. 系统配置
由于日志生产和消费通过进程间通信消息队列实现，因此需要对系统的消息队列大小进行配置

#### 1. 编辑/etc/sysctl.conf文件，增加以下三项配置，如果已经有该项配置，修改配置为下面的值
```shell
kernel.msgmax = 104857600
kernel.msgmnb = 1048576000
kernel.msgmni = 128
```
#### 2. 配置生效
执行命令 `sysctl -p` 让配置生效



## 4. 代码配置
使用LTS PHP SDK时，有以下配置参数：

| 参数名称               | 描述                      | 类型     | 是否必填    | 默认值        |
|--------------------|-------------------------|--------|---------|------------|
| $projectId          | 华为云帐号的项目ID（project id）。 | String | 必填      |            |
| $ak        | 华为云帐号的AK。               | String | 必填      |            |
| $sk    | 华为云帐号的SK。               | String | 必填      |            |
| $region         | 云日志服务的区域。               | String | 必填      |            |
| $endpoint           | 日志上报的目的地址。              | String | 必填      |            |
| $logGroup        | 上报日志的日志组。               | String | 必填      |            |
| $logStream       | 上报日志的日志流。               | String | 必填      |            |

## 5. 代码示例
```php
<?php


use HuaweiCloud\SDK\LTS\MODEL\LogMessage;
use HuaweiCloud\SDK\LTS\MODEL\LTS_Store;
use HuaweiCloud\SDK\LTS\MODEL\Config;
use function HuaweiCloud\SDK\LTS\Consumer\consumer;
use const HuaweiCloud\SDK\LTS\Consumer\MSG_TYPE;

include '../consumer/Consumer.php';
require "../../vendor/autoload.php";

$endpoint = "";
$ak = "";
$sk = "";
$projectId = "";
$region = "";
$logGroup = "";
$logStream = "";


$config = Config::getDefaultConfig();

# 这里需要启动一个子线程，子线程用来处理从主线程发送过来的日志
$pid = pcntl_fork();
if ($pid == -1) {
    // 创建失败
    exit("fork progress error!\n");
} else if ($pid == 0) {
    // 启动子进程进行日志消费，用来处理主进程推送过来的日志
    $child_pid = posix_getpid();
    var_dump("child processor $child_pid will consumer parent send log", $pid);
    consumer($config);
    exit("({$child_pid})child progress end!\n");
} else {
    global $msgQueue;
    var_dump("parent processor $pid will send log", $pid);
    for ($i = 0; $i < 10000; $i++) {
        $log = array("this is first log");
        $label = array("A" => "B");
        $log = LTS_Store::GenerateLogGroup($log, $label);
        $logmsg = new LogMessage($logGroup, $logStream, $projectId, $log);
        msg_send($msgQueue, MSG_TYPE, $logmsg, true, false);
    }
    sleep(50000);
}
```

## 6. 约束
1. 目前php-sdk缓存日志大小总数最多为500M（包括label，logstream，loggroup，日志内容等元数据信息）——单条日志一个字符的话，总共最多100w条
2. php的memory_limit设置为1024M