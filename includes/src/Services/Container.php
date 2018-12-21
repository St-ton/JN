<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services;

use Boxes\FactoryInterface;
use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\Services\GcServiceInterface;
use L10n\GetText;
use Monolog\Logger;
use OPC\DB;
use OPC\Locker;
use OPC\PageDB;
use OPC\PageService;
use OPC\Service;
use Services\JTL\AdminAccountService;
use Services\JTL\BoxServiceInterface;
use Services\JTL\CaptchaServiceInterface;
use Services\JTL\CryptoServiceInterface;
use Services\JTL\LinkServiceInterface;
use Services\JTL\NewsServiceInterface;
use Services\JTL\PasswordServiceInterface;
use Psr\Log\LoggerInterface;

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
    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->get(DbInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getPasswordService(): PasswordServiceInterface
    {
        return $this->get(PasswordServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getCryptoService(): CryptoServiceInterface
    {
        return $this->get(CryptoServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBackendLogService(): LoggerInterface
    {
        return $this->get('BackendAuthLogger');
    }

    /**
     * @inheritdoc
     */
    public function getLogService(): Logger
    {
        return $this->get('Logger');
    }

    /**
     * @inheritdoc
     */
    public function getDBServiceGC(): GcServiceInterface
    {
        return $this->get(GcServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getCache(): JTLCacheInterface
    {
        return $this->get(JTLCacheInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getLinkService(): LinkServiceInterface
    {
        return $this->get(LinkServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBoxFactory(): FactoryInterface
    {
        return $this->get(FactoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBoxService(): BoxServiceInterface
    {
        return $this->get(BoxServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getCaptchaService(): CaptchaServiceInterface
    {
        return $this->get(CaptchaServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPC(): Service
    {
        return $this->get(Service::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCPageService(): PageService
    {
        return $this->get(PageService::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCDB(): DB
    {
        return $this->get(DB::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCPageDB(): PageDB
    {
        return $this->get(PageDB::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCLocker(): Locker
    {
        return $this->get(Locker::class);
    }

    /**
     * @inheritdoc
     */
    public function getNewsService(): NewsServiceInterface
    {
        return $this->get(NewsServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getGetText(): GetText
    {
        return $this->get(GetText::class);
    }

    /**
     * @inheritdoc
     */
    public function getAdminAccount(): \AdminAccount
    {
        return $this->get(\AdminAccount::class);
    }
}
