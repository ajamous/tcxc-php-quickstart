<?php

declare(strict_types=1);

namespace TCXC\Exception;

/**
 * Exception thrown when input validation fails.
 *
 * This exception is thrown when:
 * - Phone numbers are invalid
 * - Required parameters are missing
 * - Parameter values are out of range or invalid format
 */
class ValidationException extends TCXCException
{
    /**
     * @var array<string, string[]> Validation errors
     */
    private array $errors;

    /**
     * Create a new ValidationException.
     *
     * @param string $message Error message
     * @param array<string, string[]> $errors Validation errors by field
     * @param int $code Error code
     */
    public function __construct(string $message = '', array $errors = [], int $code = 0)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if there are errors for a specific field.
     *
     * @param string $field Field name
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get errors for a specific field.
     *
     * @param string $field Field name
     * @return string[]
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
}
