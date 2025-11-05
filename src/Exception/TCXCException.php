<?php

declare(strict_types=1);

namespace TCXC\Exception;

use Exception;

/**
 * Base exception for all TCXC-related errors.
 *
 * All custom exceptions in the TCXC SDK should extend this class
 * to allow for easy exception handling and identification.
 */
class TCXCException extends Exception
{
}
