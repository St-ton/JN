<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Boxes\BoxFactoryInterface;
use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\Services\GcServiceInterface;
use Exceptions\CircularReferenceException;
use Exceptions\ServiceNotFoundException;
use Services\JTL\BoxServiceInterface;
use Services\JTL\CaptchaServiceInterface;
use Services\JTL\CryptoServiceInterface;
use Services\JTL\LinkServiceInterface;
use Services\JTL\PasswordServiceInterface;
use Psr\Log\LoggerInterface;

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
    public function getDB(): DbInterface;

    /**
     * @return PasswordServiceInterface
     */
    public function getPasswordService(): PasswordServiceInterface;

    /**
     * @return CryptoServiceInterface
     */
    public function getCryptoService(): CryptoServiceInterface;

    /**
     * @return GcServiceInterface
     */
    public function getDBServiceGC(): GcServiceInterface;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @return LoggerInterface
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getBackendLogService(): LoggerInterface;

    /**
     * @return LinkServiceInterface
     */
    public function getLinkService(): LinkServiceInterface;

    /**
     * @return BoxFactoryInterface
     */
    public function getBoxFactory(): BoxFactoryInterface;

    /**
     * @return BoxServiceInterface
     */
    public function getBoxService(): BoxServiceInterface;

    /**
     * @return CaptchaServiceInterface
     */
    public function getCaptchaService(): CaptchaServiceInterface;
}
