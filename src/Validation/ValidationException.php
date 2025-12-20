<?php

namespace App\Validation;

use Exception;

class ValidationException extends Exception
{
    public function __construct(private array $errors)
    {
        parent::__construct('Validation failed');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
