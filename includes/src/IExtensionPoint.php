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
     * @param int $id
     * @return mixed
     */
    public function init($id);
}
