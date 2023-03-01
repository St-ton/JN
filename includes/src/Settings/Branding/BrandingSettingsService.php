<?php declare(strict_types=1);

namespace JTL\Settings\Branding;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use function Functional\reindex;

/**
 * Class BrandingSettingsService
 * @package JTL\Settings
 */
class BrandingSettingsService extends AbstractService
{
    /**
     * @return void
     */
    public function initDependencies(): void
    {
        $this->repository = new BrandingSettingsRepository();
    }

    /**
     * @return array
     */
    public function getBrandingConfig(): array
    {
        $data = $this->getRepository()->getBrandingConfig();
        foreach ($data as $item) {
            $item->size         = (int)$item->size;
            $item->transparency = (int)$item->transparency;
            $item->path         = \PFAD_ROOT . \PFAD_BRANDINGBILDER . $item->path;
        }

        return reindex($data, static function ($e) {
            return $e->type;
        });
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        if (!isset($this->repository)) {
            $this->initDependencies();
        }

        return $this->repository;
    }
}
