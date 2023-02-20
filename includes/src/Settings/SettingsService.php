<?php

namespace JTL\Settings;

use JTL\DB\DbInterface;
use JTL\Shop;
use function Functional\reindex;

class SettingsService
{

    //ToDo: Service vom Abstract Service extenden sobald verfügbar
    /**
     * @var array
     */
    private array $mapping = [
        \CONF_GLOBAL              => 'global',
        \CONF_STARTSEITE          => 'startseite',
        \CONF_EMAILS              => 'emails',
        \CONF_ARTIKELUEBERSICHT   => 'artikeluebersicht',
        \CONF_ARTIKELDETAILS      => 'artikeldetails',
        \CONF_KUNDEN              => 'kunden',
        \CONF_LOGO                => 'logo',
        \CONF_KAUFABWICKLUNG      => 'kaufabwicklung',
        \CONF_BOXEN               => 'boxen',
        \CONF_BILDER              => 'bilder',
        \CONF_SONSTIGES           => 'sonstiges',
        \CONF_ZAHLUNGSARTEN       => 'zahlungsarten',
        \CONF_PLUGINZAHLUNGSARTEN => 'pluginzahlungsarten',
        \CONF_KONTAKTFORMULAR     => 'kontakt',
        \CONF_SHOPINFO            => 'shopinfo',
        \CONF_RSS                 => 'rss',
        \CONF_VERGLEICHSLISTE     => 'vergleichsliste',
        \CONF_PREISVERLAUF        => 'preisverlauf',
        \CONF_BEWERTUNG           => 'bewertung',
        \CONF_NEWSLETTER          => 'newsletter',
        \CONF_KUNDENFELD          => 'kundenfeld',
        \CONF_NAVIGATIONSFILTER   => 'navigationsfilter',
        \CONF_EMAILBLACKLIST      => 'emailblacklist',
        \CONF_METAANGABEN         => 'metaangaben',
        \CONF_NEWS                => 'news',
        \CONF_SITEMAP             => 'sitemap',
        \CONF_SUCHSPECIAL         => 'suchspecials',
        \CONF_TEMPLATE            => 'template',
        \CONF_AUSWAHLASSISTENT    => 'auswahlassistent',
        \CONF_CRON                => 'cron',
        \CONF_FS                  => 'fs',
        \CONF_CACHING             => 'caching',
        \CONF_CONSENTMANAGER      => 'consentmanager',
        \CONF_BRANDING            => 'branding'
    ];

    /**
     * @param SettingsRepository|null $repository
     */
    public function __construct(
        protected ?SettingsRepository $repository = null
    ) {
        if (\is_null($this->repository)) {
            $this->getRepository();
        }
    }

    /**
     * @return SettingsRepository
     */
    public function getRepository(): SettingsRepository
    {
        if (\is_null($this->repository)) {
            $this->repository = new SettingsRepository();
        }

        return $this->repository;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $result         = [];
        $settings       = $this->getRepository()->getAllSettings();
        $mappedSettings = $this->getMappedSettings($settings);
        foreach ($this->mapping as $mappingID => $sectionName) {
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
     * @return array
     */
    private function getMappedSettings(array $settings): array
    {
        $mappedSettings = [];
        foreach ($settings as $setting) {
            if (isset($this->mapping[(int)$setting['kEinstellungenSektion']])) {
                $mappedSettings[$this->mapping[$setting['kEinstellungenSektion']]][] = $setting;
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
    private function getBrandingConfig(): array
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
}
