<?php

namespace App\Exceptions;

use App\Exceptions\ApiException;

/**
 * Custom validation exception for API
 * Note: Laravel's built-in ValidationException is already handled in bootstrap/app.php
 * This class is kept for consistency and can be used for custom validation scenarios
 */
class ValidationException extends ApiException
{
    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}
