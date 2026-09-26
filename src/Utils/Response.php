<?php
declare(strict_types=1);

namespace Api\Utils;

class Response
{
    public static function json(int $statusCode, array $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): void
    {
        $payload = [
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        self::json($statusCode, $payload);
    }

    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $payload = [
            'status'  => 'error',
            'message' => $message
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        self::json($statusCode, $payload);
    }
}
