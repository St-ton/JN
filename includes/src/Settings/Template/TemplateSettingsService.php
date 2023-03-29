<?php declare(strict_types=1);

namespace JTL\Settings\Template;

use JTL\Abstracts\AbstractSettingsService;
use JTL\Interfaces\SettingsRepositoryInterface;

/**
 * Class TemplateSettingsService
 * @package JTL\Settings
 */
class TemplateSettingsService extends AbstractSettingsService
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
     * @return SettingsRepositoryInterface
     */
    public function getRepository(): SettingsRepositoryInterface
    {
        return $this->repository;
    }
}
