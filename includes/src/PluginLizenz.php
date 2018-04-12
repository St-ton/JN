<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Interface PluginLizenz
 */
interface PluginLizenz
{
    /**
     * @param string $cLicence
     * @return mixed
     * @deprecated since 5.0 - use IPluginLizenz instead
     */
    public function checkLicence($cLicence);
}
