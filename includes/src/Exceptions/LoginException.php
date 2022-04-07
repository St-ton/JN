<?php declare(strict_types=1);

namespace JTL\Exceptions;

use Exception;

/**
 * Class LoginException
 * @package JTL\Exceptions
 */
class LoginException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
