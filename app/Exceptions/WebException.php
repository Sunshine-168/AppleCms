<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class WebException extends Exception
{
    public function render($request): Response
    {
        return response()->view('errors.custom', [
            'message' => $this->getMessage()
        ], 500);
    }
}
