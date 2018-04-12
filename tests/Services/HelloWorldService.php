<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;


class HelloWorldService implements HelloWorldServiceInterface
{
    public function getHelloWorldString()
    {
        return " Hello World ";
    }
}