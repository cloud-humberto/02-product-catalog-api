<?php
declare(strict_types=1);

namespace Api\Controllers;

use Api\Models\User;
use Api\Utils\Response;
use Api\Utils\Validator;

class AuthController
{
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = new Validator($input);
        $validator->required('email')->email('email')->required('password');

        if (!$validator->isValid()) {
            Response::error('Validation failed for login payload.', 422, $validator->getErrors());
        }

        $user = User::findByEmail($input['email']);

        if (!$user || !password_verify($input['password'], $user['password_hash'])) {
            Response::error('Invalid email or password credentials.', 401);
        }

        $token = User::generateNewToken((int) $user['id']);

        Response::success([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email']
            ]
        ], 'Authentication successful.');
    }

    public function register(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = new Validator($input);
        $validator->required('name')->minLength('name', 3)
                  ->required('email')->email('email')
                  ->required('password')->minLength('password', 6);

        if (!$validator->isValid()) {
            Response::error('User registration validation failed.', 422, $validator->getErrors());
        }

        if (User::findByEmail($input['email'])) {
            Response::error('This email is already registered in our system.', 409);
        }

        $newUser = User::create($input['name'], $input['email'], $input['password']);

        Response::success([
            'token'      => $newUser['api_token'],
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $newUser['id'],
                'name'  => $newUser['name'],
                'email' => $newUser['email']
            ]
        ], 'User registered successfully.', 201);
    }
}
