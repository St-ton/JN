<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;


use Services\JTL\CryptoServiceInterface;
use Services\JTL\PasswordServiceInterface;

/**
 * Class ServiceLocator
 *
 * This class provides default services, that are provided by JTL-Shop core. Those Services are provided though a
 * separate interface for improving IntelliSense support for external and internal developers
 *
 * @package Services
 */
class Container extends ContainerBase implements DefaultServicesInterface
{
    public function getPasswordService()
    {
        return $this->get(PasswordServiceInterface::class);
    }

    public function getCryptoService()
    {
        return $this->get(CryptoServiceInterface::class);
    }
}