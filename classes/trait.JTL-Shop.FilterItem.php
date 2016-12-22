<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FilterItemTrait
 */
trait FilterItemTrait
{
    public function getExtraFilter()
    {
        $filter = new FilterExtra();
        Shop::dbg($this, false, 'getExtraFilter:');
    }

    /**
     * @return mixed
     */
    public function getCorrespondingBaseState()
    {
        return str_replace('FilterItem', 'FilterBase', $this->getClassName());
    }
}
