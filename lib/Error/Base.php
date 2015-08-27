<?php

namespace Kash\Error;

use Exception;

abstract class Base extends Exception
{
    public function __construct($message, $httpStatus)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->message = $message;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }
}
