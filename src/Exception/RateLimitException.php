<?php

declare(strict_types=1);

namespace TCXC\Exception;

/**
 * Exception thrown when rate limit is exceeded.
 *
 * This exception is thrown when:
 * - Too many requests are made within a time window
 * - API rate limit is exceeded
 */
class RateLimitException extends ApiException
{
    private ?int $retryAfter;

    /**
     * Create a new RateLimitException.
     *
     * @param string $message Error message
     * @param int|null $retryAfter Seconds to wait before retry
     * @param int $code Error code
     */
    public function __construct(string $message = '', ?int $retryAfter = null, int $code = 429)
    {
        parent::__construct($message, 429, null, $code);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get retry-after duration in seconds.
     *
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
