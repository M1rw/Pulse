<?php
namespace App\Core\Exceptions;

class ValidationException extends \RuntimeException
{
    private array $errors = [];

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed.');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}