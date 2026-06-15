<?php
declare(strict_types=1);

use App\Controllers\ProductController;

$router->get('/api/products', ProductController::class, 'index');
$router->get('/api/meta',     ProductController::class, 'meta');

$router->get('/api/products/{id}',ProductController::class, 'show');

$router->delete('/api/products/{id}', ProductController::class, 'destroy');
