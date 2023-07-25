<?php

namespace JTL\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * Class CircularReferenceException
 * @package JTL\Exceptions
 */
class CircularReferenceException extends \Exception implements ContainerExceptionInterface
{
    /**
     * CircularReferenceException constructor.
     * @param string $interface
     */
    public function __construct(protected $interface)
    {
        parent::__construct('Circular reference for "' . $interface . '" detected.');
    }
}
