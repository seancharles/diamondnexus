<?php
return [
    'backend' => [
        'frontName' => 'hive'
    ],
    'crypt' => [
        'key' => 'fde65e6f9097e233166e40d7955be976'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => $_ENV['DB_HOST'],
                'dbname' => $_ENV['DB_NAME'],
                'username' => $_ENV['DB_USER'],
                'password' => $_ENV['DB_ROOT_PASSWORD'],
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => '6379',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '10',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '1',
            'max_concurrency' => '15',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000'
        ]
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => $_ENV['REDIS_HOST'],
                    'database' => '11',
                    'port' => '6379'
                ]
            ],
            'page_cache' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => $_ENV['REDIS_HOST'],
                    'port' => '6379',
                    'database' => '12',
                    'compress_data' => '0'
                ]
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'google_product' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'queue' => [
        'amqp' => [
            'host' => $_ENV['RABBIT_HOST'],
            'port' => '5672',
            'user' => $_ENV['RABBIT_USER'],
            'password' => $_ENV['RABBIT_PASSWORD'],
            'virtualhost' => 'magento'
        ]
    ],
    'downloadable_domains' => [
        $_ENV['MAG_NAME'].'.1215diamonds.com'
    ],
    'install' => [
        'date' => 'Mon, 13 Jan 2020 18:43:07 +0000'
    ],
    'system' => [
        'default' => [
            'web' => [
                'unsecure' => [
                    'base_url' => 'https://'.$_ENV['MAG_NAME'].'.1215diamonds.com/'
                ],
                'secure' => [
                    'base_url' => 'https://'.$_ENV['MAG_NAME'].'.1215diamonds.com/'
                ]
            ],
            'iwd_storelocator' => [
                'api_settings' => [
                    'type' => 'google'
                ]
            ]
        ]
    ],
    'http_cache_hosts' => [
        [
            'host' => $_ENV['VARNISH_HOST']
        ]
    ]
];
