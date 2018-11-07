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
     * @return GetText
     */
    private function loadAdminLocale(): self
    {
        return $this->addLocale(PFAD_ROOT . PFAD_ADMIN . "locale");
    }

    /**
     * @param $dir
     * @return GetText
     */
    public function addAdminLocale($dir): self
    {
        return $this->addLocale(PFAD_ROOT . PFAD_ADMIN . "locale/$dir");
    }

    /**
     * @param $dir
     * @return GetText
     */
    public function addLocale($dir): self
    {
        $path = "$dir/{$this->langIso}.mo";

        if (file_exists($path)) {
            $translations = Gettext\Translations::fromMoFile("$dir/{$this->langIso}.mo");
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
}
