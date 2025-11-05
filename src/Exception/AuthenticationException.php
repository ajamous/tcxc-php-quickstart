<?php

declare(strict_types=1);

namespace TCXC\Exception;

/**
 * Exception thrown when authentication fails.
 *
 * This exception is thrown when:
 * - API credentials are invalid
 * - API signature verification fails
 * - Authentication token is expired or missing
 */
class AuthenticationException extends TCXCException
{
}
