<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterBaseTrait
 */
trait FilterBaseTrait
{
    public function getExtraFilter()
    {
        $filter = new FilterExtra();
        Shop::dbg($this, false, 'getExtraFilter:');
    }
}
