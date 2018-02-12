<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class AttributeHelper
 */
class AttributeHelper
{
    /**
     * @param string        $attribute
     * @param string        $value
     * @param callable|null $callback
     * @return mixed
     * @since 4.07
     */
    public static function getDataByAttribute($attribute, $value, callable $callback = null)
    {
        $res = Shop::DB()->select('tmerkmal', $attribute, $value);

        return is_callable($callback)
            ? $callback($res)
            : $res;
    }
}
