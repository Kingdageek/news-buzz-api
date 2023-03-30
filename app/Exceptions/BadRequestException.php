<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class BadRequestException extends Exception
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $message = $exception->getMessage();
        return response()->json([
            'message' => $message,
            'error' => $message,
            'status' => false
        ], 400);
    }
}
