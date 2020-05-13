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
     * @param DbInterface $db
     * @param string      $id
     * @param Release     $release
     */
    public function __construct(DbInterface $db, string $id, Release $release)
    {
        $this->setID($id);
        $model = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($model->getTemplate() === $id) {
            $installedVersion = Version::parse($model->getVersion());
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
        }
    }
}
