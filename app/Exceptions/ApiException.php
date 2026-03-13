<?php
namespace App\Exceptions\Api;

use Exception;

class ApiException extends Exception
{
    protected int $status = 200;

    public function render($request)
    {
        return response()->json([
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => null
        ], $this->status);
    }
}
