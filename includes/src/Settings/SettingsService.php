<?php

namespace JTL\Settings;

use JTL\Abstracts\AbstractService;
use JTL\DB\DbInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Shop;
use function Functional\reindex;

class SettingsService extends AbstractService
{
    public function initRepository(): void
    {
        $this->repository = new SettingsRepository();
    }

    /**
     * @param array $mappings
     * @return array
     */
    public function getAll(array $mappings): array
    {
        $result         = [];
        $settings       = $this->getRepository()->getAllSettings();
        $mappedSettings = $this->getMappedSettings($settings, $mappings);
        foreach ($mappings as $sectionName) {
            if (isset($mappedSettings[$sectionName])) {
                $result[$sectionName] = [];
                foreach ($mappedSettings[$sectionName] as $setting) {
                    if ($setting['type'] !== 'pass') {
                        if ($setting['type'] === 'listbox') {
                            $result[$sectionName][$setting['cName']][] = $this->getCompleteParsedSettings($setting);
                        } else {
                            $result[$sectionName][$setting['cName']] = $this->getCompleteParsedSettings($setting);
                        }
                    }
                }
            }
        }
        $result['template'] = $this->getTemplateConfig();
        $result['branding'] = $this->getBrandingConfig();

        return $result;
    }

    /**
     * @param array $settings
     * @param array $mappings
     * @return array
     */
    private function getMappedSettings(array $settings, array $mappings): array
    {
        $mappedSettings = [];
        foreach ($settings as $setting) {
            if (isset($mappings[(int)$setting['kEinstellungenSektion']])) {
                $mappedSettings[$mappings[$setting['kEinstellungenSektion']]][] = $setting;
            }
        }

        return $mappedSettings;
    }

    /**
     * @param mixed $setting
     * @return mixed
     */
    protected function getCompleteParsedSettings(array $setting): mixed
    {
        return match ($setting['type']) {
            'number' => (int)$setting['cWert'],
            'pass' => \rtrim(Shop::Container()->getCryptoService()->decryptXTEA($setting['cWert'])),
            default => $setting['cWert'],
        };
    }

    /**
     * @return array
     */
    private function getTemplateConfig(): array
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

    public function getRepository(): RepositoryInterface
    {
        if (!isset($this->repository)) {
            $this->initRepository();
        }

        return $this->repository;
    }
}
