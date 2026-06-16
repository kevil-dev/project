<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Config;
use App\Core\Validator;
use App\Models\UserModel;
use Firebase\JWT\JWT;

class AuthController
{
    private UserModel $userModel;

    public function __construct(Database $db)
    {
        $this->userModel = new UserModel($db);
    }

    public function login(Request $request, array $params): Response
    {
        $body     = $request->json();
        $email    = isset($body['email']) ? trim($body['email']) : '';
        $password = $body['password'] ?? '';

        // Basic presence checks
        $validator = new Validator();
        $validator->required('email', $email);
        $validator->required('password', $password);

        if ($validator->hasErrors()) {
            $response = new Response();
            $response->setStatus(422);
            $response->json(['errors' => $validator->getErrors()]);
            return $response;
        }

        $user = $this->userModel->findByEmail($email);

        $invalid = static function (): Response {
            $r = new Response();
            $r->setStatus(401); // 401 = "not authenticated"
            $r->json(['errors' => ['general' => 'Invalid email or password']]);
            return $r;
        };

        if ($user === null) {
            return $invalid();
        }

        if (!password_verify($password, $user['password_hash'])) {
            return $invalid();
        }

        if ((int) $user['is_active'] !== 1) {
            $response = new Response();
            $response->setStatus(403); // 403 = "I know who you are, but you're not allowed"
            $response->json(['errors' => ['general' => 'Account is disabled']]);
            return $response;
        }

        $config = Config::getInstance();
        $secret = $config->get('jwt.secret');
        $ttl    = (int) $config->get('jwt.ttl');

        $now     = time();
        $payload = [
            'sub' => (int) $user['id'],   
            'iat' => $now,                
            'exp' => $now + $ttl,         
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        $response = new Response();
        $response->json([
            'data' => [
                'token'      => $token,
                'expires_in' => $ttl,
            ],
        ]);
        return $response;
    }
}