<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Plugin;

/**
 * Interface LicenseInterface
 * @package JTL\Plugin
 */
interface LicenseInterface
{
    /**
     * @param string $cLicence
     * @return mixed
     */
    public function checkLicence($cLicence);
}
