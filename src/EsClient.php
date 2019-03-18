<?php
// +----------------------------------------------------------------------
// | Created by PhpStorm.©️
// +----------------------------------------------------------------------
// | User: 程立弘©️
// +----------------------------------------------------------------------
// | Date: 2019-03-11 00:28
// +----------------------------------------------------------------------
// | Author: 程立弘 <1019759208@qq.com>©️
// +----------------------------------------------------------------------

namespace Lsclh\ElasticsearchLib;


use EasySwoole\Component\Singleton;
use Elasticsearch\ClientBuilder;

class EsClient
{
    use Singleton;
    private $ip = '127.0.0.1';
    private $port = '9502';
    public $esClient = null;
    private function __construct($config){
        //链接
        $this->ip = $config['ip'];
        $this->port = $config['port'];
        $this->esClient = ClientBuilder::create()->setHosts([$this->ip.':'.$this->port])->build();

    }


    public function __call($name, $arguments)
    {
        return $this->esClient->$name(...$arguments);
    }

}