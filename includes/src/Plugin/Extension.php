<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

/**
 * Class Extension
 * @package Plugin
 */
class Extension extends AbstractExtension
{
    /**
     * @return string
     * @todo
     */
    public function getCurrentVersion(): string
    {
        return $this->getMeta()->getVersion();
    }
}
