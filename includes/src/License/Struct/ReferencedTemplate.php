<?php declare(strict_types=1);

namespace JTL\License\Struct;

use JTL\DB\DbInterface;
use JTL\Shop;
use JTL\Template\Admin\Listing;
use JTL\Template\Admin\ListingItem;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTLShop\SemVer\Version;
use stdClass;

/**
 * Class ReferencedTemplate
 * @package JTL\License\Struct
 */
class ReferencedTemplate extends ReferencedItem
{
    /**
     * ReferencedTemplate constructor.
     * @param DbInterface $db
     * @param stdClass    $license
     * @param Release     $release
     * @throws \Exception
     */
    public function __construct(DbInterface $db, stdClass $license, Release $release)
    {
        $exsid = $license->exsid;
        $model = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($model->getExsID() === $exsid) {
            $installedVersion = Version::parse($model->getVersion());
            $this->setID($model->getCTemplate());
            $this->setMaxInstallableVersion($release->getVersion());
            $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
            $this->setInstalled(true);
            $this->setInstalledVersion($installedVersion);
            $this->setActive(true);
        } else {
            $lstng = new Listing($db, new TemplateValidator($db));
            foreach ($lstng->getAll() as $template) {
                /** @var ListingItem $template */
                if ($template->getExsID() === $exsid) {
                    $installedVersion = Version::parse($template->getVersion());
                    $this->setID($template->getPath());
                    $this->setMaxInstallableVersion($release->getVersion());
                    $this->setHasUpdate($installedVersion->smallerThan($release->getVersion()));
                    $this->setInstalled(true);
                    $this->setInstalledVersion($installedVersion);
                    $this->setActive(true);
                    break;
                }
            }
        }
    }
}
