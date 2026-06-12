<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ProductController;

$router->get('/',             HomeController::class, 'index');
$router->get('/hello/{name}', HomeController::class, 'hello');

$router->get('/api/products', ProductController::class, 'index');
$router->get('/api/meta',     ProductController::class, 'meta');
