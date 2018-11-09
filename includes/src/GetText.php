<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class GetText
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
     * GetText constructor.
     */
    private function __construct()
    {
        $this->translator = new Gettext\Translator();
        $this->translator->register();

        $this->setLangIso(Shop::getLanguage(true))
             ->loadAdminLocale();
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
        return $this->addLocale(PFAD_ROOT . PFAD_ADMIN, $domain);
    }

    /**
     * @param string $domain
     * @param Plugin $plugin
     * @return GetText
     */
    public function loadPluginLocale(string $domain, \Plugin $plugin): self
    {
        return $this->addLocale($plugin->cAdminmenuPfad, $domain);
    }

    /**
     * @param string $path
     * @return GetText
     */
    public function addLocale(string $dir, string $domain): self
    {
        $path = "{$dir}locale/{$this->langIso}/{$domain}.mo";

        if (file_exists($path)) {
            $translations = Gettext\Translations::fromMoFile($path);
//            $translations->setDomain($domain);
            $this->translator->loadTranslations($translations);
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
}
