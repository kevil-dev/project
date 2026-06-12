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

];
