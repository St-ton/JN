<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\L10n;

use Gettext\Translations;
use Gettext\Translator;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\PluginInterface;
use JTL\Shop;

/**
 * Class GetText
 * @package JTL\L10n
 */
class GetText
{
    /**
     * @var null|string
     */
    private $langIso;

    /**
     * @var null|\Gettext\Translator
     */
    private $translator;

    /**
     * @var array locale-path => true
     */
    private $loadedPoFiles = [];

    /**
     * @var Translations[]
     */
    private $translations = [];

    /**
     * GetText constructor.
     */
    public function __construct()
    {
        $this->setLangIso($_SESSION['AdminAccount']->cISO ?? Shop::getLanguageCode() ?? 'ger')
             ->loadAdminLocale();
    }

    /**
     * @return string
     */
    public function getAdminDir(): string
    {
        return \PFAD_ROOT . \PFAD_ADMIN;
    }

    /**
     * @param PluginInterface $plugin
     * @return string
     */
    public function getPluginDir(PluginInterface $plugin): string
    {
        return $plugin->getPaths()->getBasePath();
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return string
     */
    public function getMoPath(string $dir, string $domain): string
    {
        return $dir . 'locale/' . $this->langIso . '/' . $domain . '.mo';
    }

    /**
     * @param string $domain
     * @return string
     */
    public function getAdminMoPath(string $domain): string
    {
        return $this->getMoPath($this->getAdminDir(), $domain);
    }

    /**
     * @param string          $domain
     * @param PluginInterface $plugin
     * @return string
     */
    public function getPluginMoPath(string $domain, PluginInterface $plugin): string
    {
        return $this->getMoPath($this->getPluginDir($plugin), $domain);
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return Translations
     */
    public function getTranslations(string $dir, string $domain): Translations
    {
        $path = $this->getMoPath($dir, $domain);

        if (!isset($this->translations[$path])) {
            $this->translations[$path] = Translations::fromMoFile($path);
        }

        return $this->translations[$path];
    }

    /**
     * @param string $domain
     * @return Translations
     */
    public function getAdminTranslations(string $domain): Translations
    {
        return $this->getTranslations($this->getAdminDir(), $domain);
    }

    /**
     * @param string $langIso
     * @return $this
     */
    public function setLangIso(string $langIso): self
    {
        if ($this->langIso !== $langIso) {
            $this->translations = [];
            $this->translator   = new Translator();
            $this->translator->register();
        }

        $this->langIso = $langIso;

        return $this;
    }

    /**
     * @param string $domain
     * @return GetText
     */
    public function loadAdminLocale(string $domain = 'base'): self
    {
        return $this->addLocale($this->getAdminDir(), $domain);
    }

    /**
     * @param string          $domain
     * @param PluginInterface $plugin
     * @return GetText
     */
    public function loadPluginLocale(string $domain, PluginInterface $plugin): self
    {
        return $this->addLocale($this->getPluginDir($plugin), $domain);
    }

    /**
     * @param string      $domain
     * @param ListingItem $item
     * @return GetText
     */
    public function loadPluginItemLocale(string $domain, ListingItem $item): self
    {
        $dir = \PFAD_ROOT . \PLUGIN_DIR . $item->getDir() . '/';

        return $this->addLocale($dir, $domain);
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return GetText
     */
    public function addLocale(string $dir, string $domain): self
    {
        $path = $this->getMoPath($dir, $domain);

        if (\array_key_exists($path, $this->loadedPoFiles)) {
            return $this;
        }

        if (\file_exists($path)) {
            $this->translator->loadTranslations($this->getTranslations($dir, $domain));
            $this->loadedPoFiles[$path] = true;
        }

        return $this;
    }

    /**
     * @param string $string
     * @return string
     */
    public function translate(string $string): string
    {
        return $this->translator->gettext($string);
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function changeCurrentDomain(string $domain): self
    {
        $this->translator->defaultDomain($domain);

        return $this;
    }
    /**
     * @param bool $withGroups
     * @param bool $withSections
     */
    public function loadConfigLocales(bool $withGroups = false, bool $withSections = false): void
    {
        $this->loadAdminLocale('configs/configs')
             ->loadAdminLocale('configs/values')
             ->loadAdminLocale('configs/groups');

        if ($withGroups) {
            $this->loadAdminLocale('configs/groups');
        }

        if ($withSections) {
            $this->loadAdminLocale('configs/sections');
        }
    }

    /**
     * @param object $config
     */
    public function localizeConfig($config): void
    {
        if ($config->cConf === 'Y') {
            $config->cName         = __($config->cWertName . '_name');
            $config->cBeschreibung = __($config->cWertName . '_desc');

            if ($config->cBeschreibung === $config->cWertName . '_desc') {
                $config->cBeschreibung = '';
            }
        } elseif ($config->cConf === 'N') {
            $config->cName = __('configgroup_' . $config->kEinstellungenConf);
        }
    }

    /**
     * @param object[] $configs
     */
    public function localizeConfigs(array $configs): void
    {
        foreach ($configs as $config) {
            $this->localizeConfig($config);
        }
    }

    /**
     * @param object $config
     * @param object $value
     */
    public function localizeConfigValue($config, $value): void
    {
        $value->cName = __($config->cWertName . '_value(' . $value->cWert . ')');
    }

    /**
     * @param object $config
     * @param object[] $values
     */
    public function localizeConfigValues($config, $values): void
    {
        foreach ($values as $value) {
            $this->localizeConfigValue($config, $value);
        }
    }

    /**
     * @param object $section
     */
    public function localizeConfigSection($section): void
    {
        $section->cName = __('configsection_' . $section->kEinstellungenSektion);
    }

    /**
     * @param object[] $sections
     */
    public function localizeConfigSections($sections): void
    {
        foreach ($sections as $section) {
            $this->localizeConfigSection($section);
        }
    }
}
