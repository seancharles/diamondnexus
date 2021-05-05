#!/bin/bash
#localhost=`curl ifconfig.me`
HTTP_CACHE_HOSTS="    ],
    'http_cache_hosts' => [
" 
#HTTP_CACHE_HOSTS=$HTTP_CACHE_HOSTS"        [\n            'host' => '$localhost'\n        ],\n" 
for x in `dig $MAGENTO +short` 
do 
  HTTP_CACHE_HOSTS=$HTTP_CACHE_HOSTS"        [
            'host' => '$x'
        ],
" 
done 
HTTP_CACHE_HOSTS=$HTTP_CACHE_HOSTS"    ],
];" 
echo $HTTP_CACHE_HOSTS 
