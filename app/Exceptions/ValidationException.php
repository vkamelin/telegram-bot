<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

final class ValidationException extends Exception
{
    public function __construct(private array $errors = [], string $message = 'Validation failed', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
