<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Exceptions;


use Psr\Container\ContainerExceptionInterface;

class CircularReferenceException extends \Exception implements ContainerExceptionInterface
{
    protected $interface;

    public function __construct($interface)
    {
        $this->interface = $interface;
        parent::__construct("Circular reference for '$interface' detected.");
    }
}