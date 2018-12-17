<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace L10n;

use Gettext\Translations;
use Gettext\Translator;
use Plugin\AbstractExtension;

/**
 * Class GetText
 * @package L10n
 */
class GetText
{
    /**
     * @var null|self
     */
    private static $instance;

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
     * GetText constructor.
     */
    private function __construct()
    {
        $this->translator = new Translator();
        $this->translator->register();

        if (!isset($_SESSION['AdminAccount'])) {
            $_SESSION['AdminAccount'] = new \stdClass();
        }

        if (empty($_SESSION['AdminAccount']->kSprache)) {
            $_SESSION['AdminAccount']->kSprache = \Shop::getLanguage();
        }

        $this->setLangIso(\Shop::Lang()->getIsoFromLangID($_SESSION['AdminAccount']->kSprache)->cISO)
             ->loadAdminLocale('base');
    }

    /**
     * @return GetText
     */
    public static function getInstance(): self
    {
        return self::$instance ?? (self::$instance = new self());
    }

    /**
     * @param string $langIso
     * @return $this
     */
    private function setLangIso(string $langIso): self
    {
        $this->langIso = $langIso;

        return $this;
    }

    /**
     * @param string $domain
     * @return GetText
     */
    public function loadAdminLocale(string $domain = 'base'): self
    {
        return $this->addLocale(\PFAD_ROOT . \PFAD_ADMIN, $domain);
    }

    /**
     * @param string            $domain
     * @param AbstractExtension $plugin
     * @return GetText
     */
    public function loadPluginLocale(string $domain, AbstractExtension $plugin): self
    {
        return $this->addLocale($plugin->getPaths()->getBasePath(), $domain);
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return GetText
     */
    public function addLocale(string $dir, string $domain): self
    {
        $path = $dir . 'locale/' . $this->langIso . '/' . $domain . '.mo';

        if (array_key_exists($path, $this->loadedPoFiles)) {
            return $this;
        }

        if (file_exists($path)) {
            $translations = Translations::fromMoFile($path);
            $this->translator->loadTranslations($translations);
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
    public function loadConfigLocales(bool $withGroups = false, bool $withSections = false)
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
    public function localizeConfig($config)
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
    public function localizeConfigs(array $configs)
    {
        foreach ($configs as $config) {
            $this->localizeConfig($config);
        }
    }

    /**
     * @param object $config
     * @param object $value
     */
    public function localizeConfigValue($config, $value)
    {
        $value->cName = __($config->cWertName . '_value(' . $value->cWert . ')');
    }

    /**
     * @param object $config
     * @param object[] $values
     */
    public function localizeConfigValues($config, $values)
    {
        foreach ($values as $value) {
            $this->localizeConfigValue($config, $value);
        }
    }

    /**
     * @param object $section
     */
    public function localizeConfigSection($section)
    {
        $section->cName = __('configsection_' . $section->kEinstellungenSektion);
    }

    /**
     * @param object[] $sections
     */
    public function localizeConfigSections($sections)
    {
        foreach ($sections as $section) {
            $this->localizeConfigSection($section);
        }
    }
}
