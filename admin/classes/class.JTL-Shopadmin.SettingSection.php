<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

class SettingSection
{
    public $hasSectionMarkup = false;
    public $hasValueMarkup   = false;

    private static $instances = [];

    /**
     * @param int $kSektion
     * @return static
     */
    public static function getInstance($kSektion)
    {
        if (!isset(self::$instances[$kSektion])) {
            $oSektion = Shop::DB()->select('teinstellungensektion', 'kEinstellungenSektion', $kSektion);
            if (isset($oSektion->kEinstellungenSektion)) {
                $className = 'SettingSection' . preg_replace(['([üäöÜÄÖ])', '/[^a-zA-Z_]/'], ['$1e', ''], $oSektion->cName);
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
    public function validate($conf, &$confValue)
    {
        return true;
    }

    /**
     * @param object $conf
     * @param mixed $value
     * @return void
     */
    public function setValue(&$conf, $value)
    {
        null;
    }

    /**
     * @return string
     */
    public function getSectionMarkup()
    {
        return '';
    }

    /**
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf)
    {
        return '';
    }
}
