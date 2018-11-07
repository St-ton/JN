<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SettingSection
 */
class SettingSection
{
    /**
     * @var bool
     */
    public $hasSectionMarkup = false;

    /**
     * @var bool
     */
    public $hasValueMarkup = false;

    /**
     * @var SettingSection[]
     */
    private static $instances = [];

    /**
     * @param int $kSektion
     * @return static
     */
    public static function getInstance(int $kSektion)
    {
        if (!isset(self::$instances[$kSektion])) {
            $oSektion = Shop::Container()->getDB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
            if (isset($oSektion->kEinstellungenSektion)) {
                $className = 'SettingSection' . preg_replace(
                    ['([üäöÜÄÖ])', '/[^a-zA-Z_]/'],
                    ['$1e', ''],
                    $oSektion->cName
                );
                if (class_exists($className)) {
                    self::$instances[$kSektion] = new $className();
                } else {
                    self::$instances[$kSektion] = new static();
                }
            } else {
                self::$instances[$kSektion] = new static();
            }
        }

        return self::$instances[$kSektion];
    }

    /**
     * @param object $conf
     * @param object $confValue
     * @return bool
     */
    public function validate($conf, &$confValue): bool
    {
        return true;
    }

    /**
     * @param object $conf
     * @param mixed $value
     * @return static
     */
    public function setValue(&$conf, $value): self
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSectionMarkup(): string
    {
        return '';
    }

    /**
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf): string
    {
        return '';
    }
}
