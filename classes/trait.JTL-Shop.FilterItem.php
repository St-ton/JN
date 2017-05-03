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
    /**
     * @return mixed
     */
    public function getCorrespondingBaseState()
    {
        return str_replace('FilterItem', 'FilterBase', $this->getClassName());
    }
}
