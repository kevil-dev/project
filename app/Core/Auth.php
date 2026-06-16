<?php

declare(strict_types=1);

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    public static function check(Request $request): ?int
    {
        $token = $request->bearerToken();
        if ($token === null) {
            return null;
        }
        $secret = Config::getInstance()->get('jwt.secret');
        try {
            $payload = JWT::decode($token, new Key($secret, 'HS256'));
            return (int) $payload->sub;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
