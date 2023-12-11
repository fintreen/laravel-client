<?php

namespace Fintreen\Laravel\App\Exceptions;

use Exception;

class FintreenClientException extends Exception
{
    public const GENERAL_MESSAGE = 'Client should be initialized';

    protected $message = self::GENERAL_MESSAGE;

    protected $code = 500;
}
