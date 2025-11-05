<?php

declare(strict_types=1);

namespace TCXC\Exception;

/**
 * Exception thrown when API request fails.
 *
 * This exception is thrown when:
 * - HTTP request fails
 * - API returns an error response
 * - Network connection issues occur
 */
class ApiException extends TCXCException
{
    private ?int $httpStatusCode;
    private ?string $responseBody;

    /**
     * Create a new ApiException.
     *
     * @param string $message Error message
     * @param int|null $httpStatusCode HTTP status code
     * @param string|null $responseBody Response body
     * @param int $code Error code
     */
    public function __construct(
        string $message = '',
        ?int $httpStatusCode = null,
        ?string $responseBody = null,
        int $code = 0
    ) {
        parent::__construct($message, $code);
        $this->httpStatusCode = $httpStatusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * Get HTTP status code.
     *
     * @return int|null
     */
    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    /**
     * Get response body.
     *
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Check if this is a client error (4xx).
     *
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->httpStatusCode !== null && $this->httpStatusCode >= 400 && $this->httpStatusCode < 500;
    }

    /**
     * Check if this is a server error (5xx).
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->httpStatusCode !== null && $this->httpStatusCode >= 500;
    }
}
