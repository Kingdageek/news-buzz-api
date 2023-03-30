<?php

namespace App\Utils;

use App\Exceptions\BadRequestException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Utility
{
    public static function throwAppFormatted422($errorValue, $errorKey = 'error')
    {
        $errors = array(
            $errorKey => [$errorValue],
        );
        throw new UnprocessableEntityHttpException(json_encode($errors));
    }

    public static function throwAppBadRequest400($message)
    {
        throw new BadRequestException($message);
    }
}
