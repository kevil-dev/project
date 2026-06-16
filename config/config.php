<?php
declare(strict_types=1);

return [

    'app' => [
        'name' => 'MVC Skeleton',
        'env'  => 'development',
    ],

    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'inventory',
    ],

    'paths' => [
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
    "jwt" => [
        'secret' => 'a293327c4ad54baf47279e8446f0d032ead1d6b88bd9e270dc8b908e278eb8d1',
        'ttl' => 3600
    ]

];
