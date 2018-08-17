<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Starter;


/**
 * Interface StarterInterface
 * @package Cron\Starter
 */
interface StarterInterface
{
    /**
     * @return bool
     */
    public function start() : bool;

    /**
     * @return int
     */
    public function getTimeout(): int;

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout);

    /**
     * @return string
     */
    public function getURL(): string;

    /**
     * @param string $url
     */
    public function setURL(string $url);
}
