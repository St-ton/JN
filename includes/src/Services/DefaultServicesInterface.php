<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Backend\AdminAccount;
use Boxes\FactoryInterface;
use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\Services\GcServiceInterface;
use Debug\JTLDebugBar;
use Exceptions\CircularReferenceException;
use Exceptions\ServiceNotFoundException;
use L10n\GetText;
use Monolog\Logger;
use OPC\DB;
use OPC\Locker;
use OPC\PageDB;
use OPC\PageService;
use OPC\Service;
use Services\JTL\BoxServiceInterface;
use Services\JTL\CaptchaServiceInterface;
use Services\JTL\CryptoServiceInterface;
use Services\JTL\LinkServiceInterface;
use Services\JTL\NewsServiceInterface;
use Services\JTL\PasswordServiceInterface;
use Services\JTL\AlertServiceInterface;
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
    public function getBackendLogService() : LoggerInterface;

    /**
     * @return Service
     */
    public function getOPC(): Service;

    /**
     * @return PageService
     */
    public function getOPCPageService(): PageService;

    /**
     * @return DB
     */
    public function getOPCDB(): DB;

    /**
     * @return PageDB
     */
    public function getOPCPageDB(): PageDB;

    /**
     * @return Locker
     */
    public function getOPCLocker(): Locker;

    /**
     * @return Logger
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getLogService(): Logger;

    /**
     * @return LinkServiceInterface
     */
    public function getLinkService(): LinkServiceInterface;

    /**
     * @return FactoryInterface
     */
    public function getBoxFactory(): FactoryInterface;

    /**
     * @return BoxServiceInterface
     */
    public function getBoxService(): BoxServiceInterface;

    /**
     * @return CaptchaServiceInterface
     */
    public function getCaptchaService() : CaptchaServiceInterface;

    /**
     * @return NewsServiceInterface
     */
    public function getNewsService() : NewsServiceInterface;

    /**
     * @return AlertServiceInterface
     */
    public function getAlertService() : AlertServiceInterface;

    /**
     * @return GetText
     */
    public function getGetText() : GetText;

    /**
     * @return AdminAccount
     */
    public function getAdminAccount(): AdminAccount;

    /**
     * @return JTLDebugBar
     */
    public function getDebugBar(): JTLDebugBar;
}
