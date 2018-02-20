<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Exceptions;


use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    protected $interface;

    public function __construct($interface)
    {
        $this->interface = $interface;
        parent::__construct("The Service '$interface', could not be found.");
    }
}