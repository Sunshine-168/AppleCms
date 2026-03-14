<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected int $status = 200;

    public function render($request): JsonResponse
    {
        return response()->json([
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => null
        ], $this->status);
    }
}
