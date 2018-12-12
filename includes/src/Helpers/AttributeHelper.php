<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Helpers;

use Merkmal;
use Shop;

/**
 * Class AttributeHelper
 * @package Helpers
 * @since 5.0.0
 */
class AttributeHelper
{
    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getDataByAttribute(string $attribute, $value, callable $callback = null)
    {
        $res = Shop::Container()->getDB()->select('tmerkmal', $attribute, $value);

        return \is_callable($callback)
            ? $callback($res)
            : $res;
    }

    /**
     * @param string        $attribute
     * @param string|int    $value
     * @param callable|null $callback
     * @return mixed
     * @since 5.0.0
     */
    public static function getAtrributeByAttribute(string $attribute, $value, callable $callback = null)
    {
        $att = ($res = self::getDataByAttribute($attribute, $value)) !== null
            ? new Merkmal($res->kMerkmal)
            : null;

        return \is_callable($callback)
            ? $callback($att)
            : $att;
    }
}
