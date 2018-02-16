<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Exceptions;


class ServiceNotFoundException extends \Exception
{
    protected $interface;

    public function __construct($interface)
    {
        $this->interface = $interface;
        parent::__construct("The Service '$interface', could not be found.");
    }
}