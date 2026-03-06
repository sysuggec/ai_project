<?php
declare(strict_types=1);

namespace App\Core;

use Symfony\Component\HttpFoundation\JsonResponse;

class Response
{
    public static function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status, [
            'Content-Type' => 'application/json',
        ]);
    }

    public static function success(array $data = []): JsonResponse
    {
        return self::json(array_merge(['success' => true], $data));
    }

    public static function error(string $message, int $status = 400): JsonResponse
    {
        return self::json([
            'success' => false,
            'error' => $message,
        ], $status);
    }
}
