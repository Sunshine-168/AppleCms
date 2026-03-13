<?php
namespace App\Exceptions\Admin;

use Exception;

class AdminException extends Exception
{
    protected $code = 400;

    public function render($request)
    {
        return response()->json([
            'code' => $this->code,
            'message' => $this->getMessage(),
        ]);
    }
}
