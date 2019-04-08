<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL;

/**
 * Interface IExtensionPoint
 * @package JTL
 */
interface IExtensionPoint
{
    /**
     * @param int $kInitial
     * @return mixed
     */
    public function init($kInitial);
}
