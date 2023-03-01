<?php declare(strict_types=1);

namespace JTL\Settings\Template;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class TemplateSettingsService
 * @package JTL\Settings
 */
class TemplateSettingsService extends AbstractService
{
    /**
     * @return void
     */
    public function initDependencies(): void
    {
        $this->repository = new TemplateSettingsRepository();
    }

    /**
     * @return array
     */
    public function getTemplateConfig(): array
    {
        $data     = $this->getRepository()->getTemplateConfig();
        $settings = [];
        foreach ($data as $setting) {
            if (!isset($settings[$setting->sec])) {
                $settings[$setting->sec] = [];
            }
            $settings[$setting->sec][$setting->name] = $setting->val;
        }

        return $settings;
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
