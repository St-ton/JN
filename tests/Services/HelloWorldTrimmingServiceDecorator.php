<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;


class HelloWorldTrimmingServiceDecorator implements HelloWorldServiceInterface
{
    private $inner;

    public function __construct(HelloWorldServiceInterface $inner)
    {
        $this->inner = $inner;
    }

    public function getHelloWorldString()
    {
        return trim($this->inner->getHelloWorldString());
    }
}