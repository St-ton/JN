<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Starter;

/**
 * Class DummyStarter
 * @package JTL\Cron\Starter
 */
class DummyStarter extends AbstractStarter
{
    /**
     * @inheritdoc
     */
    public function start(): bool
    {
        return true;
    }
}
