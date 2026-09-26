<?php
declare(strict_types=1);

namespace Api\Models;

use Api\Database;
use PDO;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $name, string $email, string $password): array
    {
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("
            INSERT INTO users (name, email, password_hash, api_token)
            VALUES (:name, :email, :password_hash, :api_token)
        ");

        $stmt->execute([
            'name'          => trim($name),
            'email'         => strtolower(trim($email)),
            'password_hash' => $hash,
            'api_token'     => $token
        ]);

        return [
            'id'        => (int) $db->lastInsertId(),
            'name'      => $name,
            'email'     => $email,
            'api_token' => $token
        ];
    }

    public static function generateNewToken(int $userId): string
    {
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("UPDATE users SET api_token = :token WHERE id = :id");
        $stmt->execute(['token' => $token, 'id' => $userId]);
        return $token;
    }
}
