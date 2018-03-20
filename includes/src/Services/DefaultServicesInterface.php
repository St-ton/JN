<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\Services\GcServiceInterface;
use Exceptions\CircularReferenceException;
use Exceptions\ServiceNotFoundException;
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
     * @return DbInterface
     */
    public function getDB();

    /**
     * @return PasswordServiceInterface
     */
    public function getPasswordService();

    /**
     * @return CryptoServiceInterface
     */
    public function getCryptoService();

    /**
     * @return GcServiceInterface
     */
    public function getDBServiceGC();

    /**
     * @return JTLCacheInterface
     */
    public function getCache();
}
