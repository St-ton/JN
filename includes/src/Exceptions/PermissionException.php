<?php declare(strict_types=1);

namespace JTL\Exceptions;

use Exception;

/**
 * Class PermissionException
 * @package JTL\Exceptions
 */
class PermissionException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
