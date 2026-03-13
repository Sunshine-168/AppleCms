<?php
namespace App\Exceptions\Web;

use Exception;

class WebException extends Exception
{
    public function render($request)
    {
        return response()->view('errors.custom', [
            'message' => $this->getMessage()
        ], 500);
    }
}
