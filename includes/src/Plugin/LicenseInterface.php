<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Plugin;

/**
 * Interface LicenseInterface
 * @package Plugin
 */
interface LicenseInterface
{
    /**
     * @param string $cLicence
     * @return mixed
     */
    public function checkLicence($cLicence);
}
