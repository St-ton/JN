<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

/**
 * Interface PluginLizenz
 */
interface PluginLicenseInterface
{
    /**
     * @param string $cLicence
     * @return mixed
     */
    public function checkLicence($cLicence);
}
