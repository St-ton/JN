<?php

namespace JTL\Exceptions;

/**
 * Class InvalidEntityNameException
 * @package JTL\Exceptions
 */
class InvalidEntityNameException extends \Exception
{
    /**
     * InvalidEntityNameException constructor.
     * @param string $entityName
     */
    public function __construct(protected $entityName)
    {
        parent::__construct('Invalid entity name ' . $entityName);
    }
}
