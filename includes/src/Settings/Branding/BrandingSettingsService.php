<?php declare(strict_types=1);

namespace JTL\Settings\Branding;

use JTL\Abstracts\AbstractService;
use function Functional\reindex;

/**
 * Class BrandingSettingsService
 * @package JTL\Settings
 */
class BrandingSettingsService extends AbstractService
{
    /**
     * @var BrandingSettingsRepository
     */
    private BrandingSettingsRepository $repository;

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
        $data = $this->getRepository()->getConfig();
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
     * @return BrandingSettingsRepository
     */
    public function getRepository(): BrandingSettingsRepository
    {
        return $this->repository;
    }
}
