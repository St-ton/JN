<?php declare(strict_types=1);

namespace JTL\Settings;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Settings\Branding\BrandingSettingsService;
use JTL\Settings\Template\TemplateSettingsService;
use JTL\Shop;

/**
 * Class SettingsService
 * @package JTL\Settings
 */
class SettingsService extends AbstractService
{
    /**
     * @var BrandingSettingsService
     */
    protected BrandingSettingsService $brandingSettingsService;

    /**
     * @var TemplateSettingsService
     */
    protected TemplateSettingsService $templateSettingsService;

    /**
     * @var CryptoServiceInterface
     */
    protected CryptoServiceInterface $cryptoService;


    /**
     * @return void
     */
    protected function initDependencies(): void
    {
        $this->brandingSettingsService = new BrandingSettingsService();
        $this->templateSettingsService = new TemplateSettingsService();
        $this->cryptoService           = Shop::Container()->getCryptoService();
        $this->repository              = new SettingsRepository();
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
        $result['template'] = $this->getTemplateSettingsService()->getTemplateConfig();
        $result['branding'] = $this->getBrandingSettingsService()->getBrandingConfig();

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
            'pass' => \rtrim($this->getCryptoService()->decryptXTEA($setting['cWert'])),
            default => $setting['cWert'],
        };
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

    /**
     * @return BrandingSettingsService
     */
    public function getBrandingSettingsService(): BrandingSettingsService
    {
        if (!isset($this->brandingSettingsService)) {
            $this->initDependencies();
        }

        return $this->brandingSettingsService;
    }

    /**
     * @return TemplateSettingsService
     */
    public function getTemplateSettingsService(): TemplateSettingsService
    {
        if (!isset($this->templateSettingsService)) {
            $this->initDependencies();
        }

        return $this->templateSettingsService;
    }

    /**
     * @return CryptoServiceInterface
     */
    public function getCryptoService(): CryptoServiceInterface
    {
        if (!isset($this->cryptoService)) {
            $this->initDependencies();
        }
        return $this->cryptoService;
    }
}
