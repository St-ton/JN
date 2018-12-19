<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

/**
 * Class HelloWorldService
 * @package Services
 */
class HelloWorldService implements HelloWorldServiceInterface
{
    /**
     * @return string
     */
    public function getHelloWorldString()
    {
        return " Hello World ";
    }
}