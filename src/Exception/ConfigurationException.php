<?php

declare(strict_types=1);

namespace TCXC\Exception;

/**
 * Exception thrown when there is a configuration error.
 *
 * This exception is thrown when:
 * - Required configuration values are missing
 * - Configuration values are invalid
 * - Environment variables cannot be loaded
 */
class ConfigurationException extends TCXCException
{
}
