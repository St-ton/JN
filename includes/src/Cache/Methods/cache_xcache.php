<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Cache\Methods;


use Cache\ICachingMethod;
use Cache\JTLCacheTrait;

/**
 * Class cache_xcache
 * Implements the XCache Opcode Cache
 *
 * @warning Untested
 * @warning Does not support caching groups
 * @package Cache\Methods
 * @deprecated since 5.0.0
 */
class cache_xcache extends cache_null
{
    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function test(): bool
    {
        return false;
    }
}
