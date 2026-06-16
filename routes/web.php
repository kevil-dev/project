<?php
declare(strict_types=1);

use App\Controllers\ProductController;
use App\Controllers\AuthController;

$router->post('/api/login', AuthController::class, 'login');

$router->get('/api/products', ProductController::class, 'index');
$router->get('/api/meta',     ProductController::class, 'meta');

$router->get('/api/products/{id}',ProductController::class, 'show');

$router->delete('/api/products/{id}', ProductController::class, 'destroy', true);

$router->post('/api/products', ProductController::class, 'store', true);

$router->post('/api/products/{id}/update', ProductController::class, 'update', true);

$router->post('/api/products/{id}/stock', ProductController::class, 'stock', true);