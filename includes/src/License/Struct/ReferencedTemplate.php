<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTL\DB\DbInterface;
use JTL\Shop;
use JTLShop\SemVer\Version;

/**
 * Class ReferencedTemplate
 * @package JTL\License\Struct
 */
class ReferencedTemplate extends ReferencedItem
{
    /**
     * ReferencedPlugin constructor.
     * @param string      $exsid
     * @param Release     $release
     */
    public function __construct(string $exsid, Release $release)
    {
        $model = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($model->getExsID() === $exsid) {
            $installedVersion = Version::parse($model->getVersion());
            $this->setID($model->getCTemplate());
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
        }
    }
}
