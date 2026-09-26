<?php
declare(strict_types=1);

namespace Api\Middleware;

use Api\Database;
use Api\Utils\Response;

class AuthMiddleware
{
    public static function handle(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            Response::error('Unauthorized access. Please provide a Bearer token in the Authorization header.', 401);
        }

        $token = trim($matches[1]);

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email FROM users WHERE api_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            Response::error('Invalid or expired Bearer token.', 401);
        }

        return $user;
    }
}
