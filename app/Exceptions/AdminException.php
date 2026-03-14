<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AdminException extends Exception
{
    protected $code = 400;

    public function render($request): JsonResponse
    {
        return response()->json([
            'code' => $this->code,
            'message' => $this->getMessage(),
        ]);
    }
}
