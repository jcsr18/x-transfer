<?php

namespace App\Supports;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResponseSupport
{
    public static function httpResponse(Exception $exception, int $defaultStatus): JsonResponse
    {
        $statusCode = $exception instanceof HttpException ?
            $exception->getStatusCode() :
            $defaultStatus;

        return Response::json(['error' => $exception->getMessage()], $statusCode);
    }
}
