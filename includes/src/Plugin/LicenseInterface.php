<?php

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
