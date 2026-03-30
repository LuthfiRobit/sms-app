<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ResponseService
{
    const STATUS_SUCCESS = 200;
    const STATUS_CREATED = 201;
    const STATUS_VALIDATION_ERROR = 422;
    const STATUS_ERROR = 500;
    const STATUS_NOT_FOUND = 404;

    /**
     * Return a JSON response with a specific status code and message.
     */
    public function response($data = null, $statusCode = 200, $message = ''): JsonResponse
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a successful response with data.
     */
    public function success($data = null, $message = 'Request successful'): JsonResponse
    {
        return $this->response($data, self::STATUS_SUCCESS, $message);
    }

    /**
     * Return a validation error response.
     */
    public function validationError($errors): JsonResponse
    {
        return response()->json([
            'status' => self::STATUS_VALIDATION_ERROR,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], self::STATUS_VALIDATION_ERROR);
    }

    /**
     * Return an error response for any unexpected issues.
     */
    public function error($message = 'An error occurred', $statusCode = self::STATUS_ERROR): JsonResponse
    {
        return $this->response(null, $statusCode, $message);
    }
}
