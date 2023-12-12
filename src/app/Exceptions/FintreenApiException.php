<?php

namespace Fintreen\Laravel\app\Exceptions;

use Exception;

class FintreenApiException extends Exception
{
    public const GENERAL_MESSAGE = 'Api request exception';

    protected $message = self::GENERAL_MESSAGE;

    protected $code = 500;
}
