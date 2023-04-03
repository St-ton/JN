<?php declare(strict_types=1);

namespace JTL\Settings\Template;

use JTL\Abstracts\AbstractService;

/**
 * Class TemplateSettingsService
 * @package JTL\Settings
 */
class TemplateSettingsService extends AbstractService
{
    /**
     * @var TemplateSettingsRepository
     */
    private TemplateSettingsRepository $repository;

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
        $data     = $this->getRepository()->getConfig();
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
     * @return TemplateSettingsRepository
     */
    public function getRepository(): TemplateSettingsRepository
    {
        return $this->repository;
    }
}
