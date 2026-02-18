<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Unauthorized access')
    {
        parent::__construct($message, 401);
    }
}
