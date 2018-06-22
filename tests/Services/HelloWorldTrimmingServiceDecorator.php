<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

/**
 * Class HelloWorldTrimmingServiceDecorator
 * @package Services
 */
class HelloWorldTrimmingServiceDecorator implements HelloWorldServiceInterface
{
    private $inner;

    /**
     * HelloWorldTrimmingServiceDecorator constructor.
     * @param HelloWorldServiceInterface $inner
     */
    public function __construct(HelloWorldServiceInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @return string
     */
    public function getHelloWorldString()
    {
        return trim($this->inner->getHelloWorldString());
    }
}