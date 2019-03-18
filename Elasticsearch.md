#Elasticsearch的安装与使用
>##环境依赖
>* 1 基于java开发 需要java的sdk运行环境安装
>
>* 2 下载 [前往官网](https://www.elastic.co/cn)
>
>* 3 解压即可安装


##单机的配置与运行

>* 1 启动文件在 bin/elasticsearch
>```
>启动 
>1. cd bin
>2. elasticsearch
>守护进程启动
>1. cd bin
>2. elasticsearch -d
>```

>* 2 配置文件在 config/elasticsearch.yml

>* 3 在最下方写入(注意--配置:后面必须跟空格!!!--)
>```
>xpack.ml.enabled: false  #包不开启
>network.host: 127.0.0.1   #监听的ip
>http.port: 9502           #监听的端口
>
>#memory 内存配置
>bootstrap.memory_lock: false
>bootstrap.system_call_filter: false
>
>#跨域处理
>http.cors.enabled: true
>http.cors.allow-origin: "*"
>```

>* 4 此时已经可以启动测试了 在浏览器里访问吧


##分布式配置与运行

>通常是一台服务器上配置一台
>```
>主服务配置
>cluster.name: abc 分布式集群的名字 可任意填写 同一个集群要有相同的名字
>node.name: abc_1  每一个节点的名字 就是每一台服务的名字 同一个集群不要出现相同
>node.master: true 设置成主服务(也叫主节点)
>
>从服务配置
>cluster.name: abc 分布式集群的名字 可任意填写 同一个集群要有相同的名字
>node.name: abc_2  每一个节点的名字 就是每一台服务的名字 同一个集群不要出现相同
>node.master: false 非主服务(也叫主节点)
>discovery.zen.ping.unicast.hosts: ["127.0.0.1"] 自动查找主节点服务 填写主节点对外ip即可
>```




#Elasticsearch-head安装
>(相当于Navicat mysql 管理工具一样,用来管理elasticsearch)
>
>[前往git地址](https://github.com/mobz/elasticsearch-head)

>使用方式
>
>```
>git clone git://github.com/mobz/elasticsearch-head.git
>cd elasticsearch-head
>npm install
>npm run start
>open http://localhost:9100/
>```

>配置的更改
>
>目录下的Gruntfile.js 可以更改端口号 同时还需要更改_site/app.js里的对外端口号
>```
>//找到此处
>connect: {
>    server: {
>        options: {
>            port: 9100, //此处修改端口监听
>            base: '.',
>            keepalive: true
>        }
>    }
>}	
>```



##通过Elasticsearch-head使用Elasticsearch
索引(类似与mysql的表结构)

[点击前往文档查看数据结构](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html)

>查看 Field datatypes处
>
>a simple type like text, keyword, date, long, double, boolean or ip.
>
>类型有 text(字串) keyword(关键字 类似于主键 可以有多个) date(时间) long(整数) double(小数) boolean(真假) ip(ip)

>使用方式
```
{
  "mappings": {       固定
    "_doc": {         此处是索引type
      "properties": { 固定
        "user_id": {     此处相当于字段
          "type": "keyword"  
        },
        "name": {
          "type": "text"
        },
        "age": {
          "type": "integer"
        },
        "status": {
          "type": "byte" 
        }
      }
    }
  }
}

```

###* 1新建索引(可理解为建表)

普通索引

>
>>索引名: 随意英文名
>
>>分片数: 建议为节点数(几台服务器)的1.5到3倍 整数
>
>>副本数: 1即可
>
>>切换到主页
>>此时会发现分布式集群 副本与节点会落在不同的服务上 属于非结构化
>>
>>加粗的是分片 浅色的是副本

创建结构化索引


```
{
  "mappings": {       
    "abc": {         
      "properties": { 
        "user_id": {     
          "type": "keyword"  
        },
        "name": {
          "type": "text"
        },
        "age": {
          "type": "integer"
        },
        "status": {
          "type": "byte" 
        }
      }
    }
  },
  "settings": {
      "number_of_shards": 2, 
      "number_of_replicas": 0 
  }
}

索引名在上方的url处修改
如 http://localhost:9502/video_test(索引名)
请求方式改为PUT 左边的输入框留空
勾上易读点击提交 会默认创建5个切片 与 1个副本
还可以通过setting修改
{
    "settings": {
        "number_of_shards": 2, 切片
        "number_of_replicas": 0 副本
    }
}


完整版样式

http://localhost:9502/video_test
________________________________ PUT
{
  "mappings": {       
    "abc": {         
      "properties": { 
        "user_id": {     
          "type": "keyword"  
        },
        "name": {
          "type": "text"
        },
        "age": {
          "type": "integer"
        },
        "status": {
          "type": "byte" 
        }
      }
    }
  },
  "settings": {
      "number_of_shards": 2, 
      "number_of_replicas": 0 
  }
}

提交请求[btn]  验证json[btn]  [redio]易读
```

###* 2 添加数据(insert,也叫添加文档,一条数据就是一个文档)


>1.自增id   **POST**
```
http://localhost:9502/索引名/mapping下面的索引type

http://localhost:9502/video_test/abc
___________________________ POST
{
  "user_id": "1",
  "name": "牛逼",
  "age": 18,
  "status": 1
}

提交请求[btn]  验证json[btn]  [redio]易读


返回值

{
  "_index": "video_test",
  "_type": "video",
  "_id": "VEbjZ2kB19gxdwkPA9d7", //文档自动生成的id
  "_version": 1,
  "result": "created",
  "_shards": {
    "total": 1,
    "successful": 1,
    "failed": 0
  },
  "_seq_no": 2,
  "_primary_term": 1
}


```

>2.指定id **PUT**

```
http://localhost:9502/索引名/mapping下面的索引type/指定id为1

http://localhost:9502/video_test/abc/1
___________________________ PUT
{
  "user_id": "1",
  "name": "牛逼",
  "age": 18,
  "status": 1
}

提交请求[btn]  验证json[btn]  [redio]易读


返回值
{
  "_index": "video_test", //索引名
  "_type": "video",       //索引类型
  "_id": "1",             //文档指定的id
  "_version": 1,
  "result": "created",    //created表示创建成功
  "_shards": {
    "total": 1,
    "successful": 1,
    "failed": 0
  },
  "_seq_no": 0,
  "_primary_term": 1
}
```
此时可以点击数据浏览 就会显示一个像mysql的表一样的页面 里面就有添加进去的数据


###* 3 查询数据(select)

>1.输入分词
```
http://localhost:9502/索引名/mapping下面的索引type

http://localhost:9502/video_test/abc
_search                             POST

{
  "query": {
    "match": {
      "name": "牛逼啊"
    }
  }
}

会把带牛,逼,啊 三个字(不一定相邻)的全部搜索出来进行匹配度排序

```
>2.不分词


```
http://localhost:9502/索引名/mapping下面的索引type

http://localhost:9502/video_test/abc
_search                             POST

{
  "query": {
    "match_phrase": {
      "name": "牛"
    }
  }
}
会把带 牛逼啊 (相当于like紧邻不分词)的全部搜索出来进行匹配度排序 

```

>3.聚合查询
```
分组统计
http://localhost:9502/video_test/abc
_search                             POST
{
  "aggs": {
    "group_user": {
      "terms": {
        "field": "user_id"
      }
    }
  }
}
会把user_id分组count
相当于 SELECT count(id) as num,user_id from video_test-abc group user_id


返回部分

{
    ......省略
  "aggregations": {
    "group_user": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 0,
      "buckets": [
        {
          "key": "2", 值
          "doc_count": 8  出现次数
        },
        {
          "key": "1",
          "doc_count": 1
        }
      ]
    }
  }
}

```


##PHP使用Elasticsearch

[git PHP_SDK链接](https://github.com/elastic/elasticsearch-php)

使用composer安装sdk

```
"require": {
    "elasticsearch/elasticsearch": "~6.0"
}

执行composer update即可

  - Installing psr/log (1.1.0): Downloading (100%)         
  - Installing react/promise (v2.7.1): Downloading (100%)         
  - Installing guzzlehttp/streams (3.0.0): Downloading (100%)         
  - Installing guzzlehttp/ringphp (1.1.1): Downloading (100%)         
  - Installing elasticsearch/elasticsearch (v6.1.0): Downloading (100%)   
  elasticsearch/elasticsearch suggests installing monolog/monolog (Allows for client-level logging and tracing)
  
  安装完成
  
```

使用
```
//首先获取链接
$client = ClientBuilder::create()->setHosts(["127.0.0.1:9502"])->build(); 
```
搜索
```
  1.不分词
  $params = [
      'index'=>'video_test', //索引名      理解为库名
      'type'=>'video',       //索引类型     理解为表名
      'body'=>[              //数据
          'query'=>[
              'match_phrase'=>[   //不分词匹配
                  'name'=>'刺战激'
              ]
          ]
      ]
  ];
  2.分词
  $params = [
        'index'=>'video_test', //索引名      理解为库名
        'type'=>'video',       //索引类型     理解为表名
        'body'=>[              //数据
            'query'=>[
                'match'=>[   //分词匹配
                    'name'=>'刺战激'
                ]
            ]
        ]
  ];

  3.多字段不同次查询
  $params = [
      'index' => 'video_test',
      'type' => 'video',
      'body'=> [
  		'query'	=> [
  			'bool'	=> [
  				'选填'	=> [
  					['match'	=> ['name'	=> '刺激']],
  					['match'	=> ['content'	=> '准']]
  				]
  			]
  			
  		]
  
  	]
      
  ];
  4.多字段相同词查询
  $params = [
        'index' => 'video_test',
        'type' => 'video',
        'body'=> [
    		'query'	=> [
    			'multi_match'=> [
    				'query'	=> '刺激啊',
    				'fields' => ['name','content']
    			]
    			
    		]
    
    	]
        
    ];
  
  
  5.分页
  $params = [
        'index' => 'video_test',
        'type' => 'video',
        'body'=> [
    		'query'	=> [
    			'bool'	=> [
    				'随意'	=> [
    					['match'	=> ['name'	=> '刺激']],
    					['match'	=> ['content'	=> '准']]
    				]
    			]
    			
    		]
    
    	],
    	'size'=>16, //每页10条
    	'from'=>200 //已经到多少条
    ];
    6.条件
    //range:范围查询关键字
    //gte:大于等于
    //gt:大于
    //lte:小于等于
    //lt：小于
    $params = [
            'index' => 'video_test',
            'type' => 'video',
            'body'=> [
        		'query'	=> [
        			'bool'	=> [
        				'随意'	=> [
        					['match'	=> ['name'	=> '刺激']],
        					['match'	=> ['content'	=> '准']]
        				]
        			],
        			'range'=>[
        			    'id' => ['gte' => 20,'lt' => 30]
        			]
        			
        		]
        
        	],
        	'size'=>16, //每页10条
        	'from'=>200 //已经到多少条
    ];    
  7.数据过滤
  $params = [
          'index' => 'video_test',
          'type' => 'video',
          'body'=> [
            'query'	=> [
                'bool'	=>[
                    'filter'=>['term'=>['word_count'=>2222]]
                ]
            ]
        ],
        
  ]; 
  
  $client->search($params);
```

获取与删除
```
  1.获取
  $params = [
      'index'=>'video_test',
      'type'=>'video',
      'id'=>1
  ];
  $res = $client->get($params);
  
  2.删除
  $client->delete($params);
```

创建索引与更新索引
```
同一条id为更新 不存在的id为创建
$params = [
    'index' => 'video_test',
    'type' => 'video',
    'id' => '10',
    'body' => [
		'name' => '哈哈',
		'content'	=> '你好,从纵向即文档这个维度来看，每列代表文档包含了哪些单词。'
	]
];
$res=$client->index($params);
```

批量索引

```
$params = [
    'index' => 'video_test',
    'type' => 'video',
    'body' => [
        ['index' => [ '_id' => 1]],
        [
            'name' => 'abc',
            'title' => '你好,从纵向即文档这个维度来看，每列代表文档包含了哪些单词，。',
            'content' => '中国非常强大的哈哈哈,不错,及时这个时代的步伐,研究红色呢过名生命科学'
        ],
        
        
        ['index' => [ '_id' => 2]],
        [
            'name' => '青山绿水',
            'title' => '无名英雄',
            'content' => '力量是非常强大'
        ],
        
        
        ['index' => [ '_id' => 3]],
        [
            'name' => 'abc',
            'title' => '代码的力量',
            'content' => '天天上学'
        ]
    ]
];

$res=$client->bulk($params);
```

聚合计算

```
$params = [
    'index' => 'video_test',
    'type' => 'video',
    'body' => [
        'aggs'=>[
            'avg_money'=>[
                'avg'=>['field':'money']
            ]
        ]
    ]
]
    
```





##IK分词器
[安装git地址](https://github.com/medcl/elasticsearch-analysis-ik)

>安装

```
optional 1 - download pre-build package from here: https://github.com/medcl/elasticsearch-analysis-ik/releases
//先去下载压缩包
create plugin folder cd your-es-root/plugins/ && mkdir ik
//在你的es/plugins目录下 创建一个ik目录
unzip plugin to folder your-es-root/plugins/ik
//解压下载的源码包到ik目录
完成
访问
http://服务地址/_cat/plugins
进行测试 出现 J2Gobmp analysis-ik 6.6.1 就可以了
```


```
索引创建格式
http://47.100.40.15:9502/ydty/
______________________________ PUT

{
  "mappings": {
    "product": {
      "properties": {
        "price_type": {
          "type": "keyword"
        },
        "integral_type": {
          "type": "keyword"
        },
        "promote_status": {
          "type": "keyword"
        },
        "sale_status": {
          "type": "keyword"
        },
        "title": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        },
        "category_name": {
          "type": "text",
          "analyzer": "ik_max_word",
          "search_analyzer": "ik_max_word"
        }
      }
    }
  },
  "settings": {
    "number_of_shards": 2,
    "number_of_replicas": 0
  }
}
```


搜索时可以添加字段来做高亮显示
```
{
	"query": {
		"bool": {
			"must": {
				"multi_match": {
					"query": "\u4e2d\u56fd\u534e\u4e3a",
					"fields": ["category_name", "title"]
				}
			},
			"filter": [{
				"range": {
					"sale_status": {
						"gt": 0
					}
				}
			}, {
				"range": {
					"price_type": {
						"gt": 0
					}
				}
			}]
		}
	},
	"highlight": {
		"pre_tags": ["<1>"],//用于包围匹配词的开头
		"post_tags": ["<1>"],//用于包围匹配词的结尾
		"fields": { //匹配的字段
			"title": {}, //此处一定是空对象 不能使空数组 不然报错 PHP使用 new \ArrayObject() 来保证json_encode后为对象
			"category_name": {}
		}
	}
}
```










