<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Starter;


/**
 * Class AbstractStarter
 * @package Cron\Starter
 */
abstract class AbstractStarter implements StarterInterface
{
    /**
     * timeout in ms
     *
     * @var int
     */
    protected $timeout = 150;

    /**
     * @var string
     */
    protected $url;

    /**
     * @inheritdoc
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @inheritdoc
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @inheritdoc
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function setURL(string $url)
    {
        $this->url = $url;
    }
}
