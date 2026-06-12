<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class HomeController
{
    private ?Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db;
    }

    public function index(Request $request, array $params): Response
    {
        $response = new Response();
        $response->json([
            'status'  => 'ok',
            'message' => 'MVC skeleton is working',
            'php'     => PHP_VERSION,
        ]);
        return $response;
    }

    public function hello(Request $request, array $params): Response
    {
        $name     = $params['name'];
        $greeting = $request->get('greeting', 'Hello');

        $response = new Response();
        $response->json([
            'message' => $greeting . ', ' . $name . '!',
        ]);
        return $response;
    }
}
