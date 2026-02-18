<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

class NotFoundException extends ApiException
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message, 404);
    }
}
