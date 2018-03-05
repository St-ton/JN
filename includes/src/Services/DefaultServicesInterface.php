<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Exceptions\CircularReferenceException;
use Exceptions\ServiceNotFoundException;
use Services\JTL\AuthLoggerServiceInterface;
use Services\JTL\CryptoServiceInterface;
use Services\JTL\PasswordServiceInterface;

/**
 * Interface DefaultServicesInterface
 *
 * This interface provides default services, that are provided by JTL-Shop core. Those Services are provided through a
 * separate interface for improving IntelliSense support for external and internal developers
 *
 * @package Services
 */
interface DefaultServicesInterface extends ContainerInterface
{
    /**
     * @return PasswordServiceInterface
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getPasswordService();

    /**
     * @return CryptoServiceInterface
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getCryptoService();

    /**
     * @return AuthLoggerServiceInterface
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getAuthLoggerService();
}
