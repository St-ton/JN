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
use Services\JTL\BoxServiceInterface;
use Services\JTL\CaptchaServiceInterface;
use Services\JTL\CryptoServiceInterface;
use Services\JTL\LinkServiceInterface;
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
    public function getBoxFactory(): BoxFactoryInterface
    {
        return $this->get(BoxFactoryInterface::class);
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
    public function getOPC()
    {
        return $this->get(\OPC\Service::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCPageService()
    {
        return $this->get(\OPC\PageService::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCDB()
    {
        return $this->get(\OPC\DB::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCPageDB()
    {
        return $this->get(\OPC\PageDB::class);
    }

    /**
     * @inheritdoc
     */
    public function getOPCLocker()
    {
        return $this->get(\OPC\Locker::class);
    }

}
