<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Shopsetting
 */
final class Shopsetting implements ArrayAccess
{
    /**
     * @var Shopsetting
     */
    private static $_instance = null;

    /**
     * @var array
     */
    private $_container = array();

    /**
     *
     */
    private function __construct()
    {
        self::$_instance = $this;
    }

    /**
     *
     */
    private function __clone()
    {
    }

    /**
     * @return Shopsetting
     */
    public static function getInstance()
    {
        return (self::$_instance === null) ? new self() : self::$_instance;
    }

    /**
     * for rare cases when options are modified and directly re-assigned to smarty
     * do not call this function otherwise.
     *
     * @return $this
     */
    public function reset()
    {
        $this->_container = array();

        return $this;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_container[] = $value;
        } else {
            $this->_container[$offset] = $value;
        }

        return $this;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_container[$offset]);
    }

    /**
     * @param mixed $offset
     * @return $this
     */
    public function offsetUnset($offset)
    {
        unset($this->_container[$offset]);

        return $this;
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        if (!isset($this->_container[$offset])) {
            $section = $this->mapSettingName(null, $offset);

            if ($section === false || $section === null) {
                return;
            }
            $cacheID = 'setting_' . $section;

            // Dirty Template work around
            if ($section === CONF_TEMPLATE) {
                if (($templateSettings = Shop::Cache()->get($cacheID)) === false) {
                    $template         = Template::getInstance();
                    $templateSettings = $template->getConfig();
                    Shop::Cache()->set($cacheID, $templateSettings, array(CACHING_GROUP_TEMPLATE, CACHING_GROUP_OPTION));
                }
                if (is_array($templateSettings)) {
                    foreach ($templateSettings as $templateSection => $templateSetting) {
                        $this->_container[$offset][$templateSection] = $templateSetting;
                    }
                }
            } else {
                try {
                    if (($settings = Shop::Cache()->get($cacheID)) !== false) {
                        foreach ($settings as $setting) {
                            $this->_container[$offset][$setting->cName] = $setting->cWert;
                        }

                        return $this->_container[$offset];
                    }
                } catch (Exception $exc) {
                    Jtllog::writeLog("Setting Caching Exception: " . $exc->getMessage(), JTLLOG_LEVEL_ERROR);
                }
                $settings = Shop::DB()->query("
                    SELECT kEinstellungenSektion, cName, cWert
                        FROM teinstellungen
                        WHERE kEinstellungenSektion = {$section}", 2
                );
                if (is_array($settings) && count($settings) > 0) {
                    $this->_container[$offset] = array();

                    foreach ($settings as $setting) {
                        $this->_container[$offset][$setting->cName] = $setting->cWert;
                    }

                    Shop::Cache()->set($cacheID, $settings, array(CACHING_GROUP_OPTION));
                }
            }
        }

        return (isset($this->_container[$offset])) ? $this->_container[$offset] : null;
    }

    /**
     * @param array $sektionen_arr
     * @return array
     */
    public function getSettings($sektionen_arr)
    {
        $ret = array();
        if (!is_array($sektionen_arr)) {
            $sektionen_arr = (array) $sektionen_arr;
        }
        foreach ($sektionen_arr as $sektionen) {
            $mapping = self::mapSettingName($sektionen);
            if ($mapping !== null) {
                $ret[$mapping] = $this[$mapping];
            }
        }

        return $ret;
    }

    /**
     * @param null|string $section
     * @param null|string $name
     * @return mixed|null
     */
    public static function mapSettingName($section = null, $name = null)
    {
        if ($section === null && $name === null) {
            return false;
        }
        $mappings = self::getMappings();
        if ($section !== null && isset($mappings[$section])) {
            return $mappings[$section];
        }
        if ($name !== null && ($key = array_search($name, $mappings)) !== false) {
            return $key;
        }

        return;
    }

    /**
     * preload the _container variable with one single sql statement or one single cache call
     * this is being called after successful cache initialisation in class.JTL-Shop.JTLCache.php
     *
     * @return array
     */
    public function preLoad()
    {
        $cacheID = 'settings_all_preload';
        if (($result = Shop::Cache()->get($cacheID)) === false) {
            $mappings = self::getMappings();
            $settings = Shop::DB()->query("
                SELECT kEinstellungenSektion, cName, cWert
                    FROM teinstellungen
                    ORDER BY kEinstellungenSektion", 9
            );
            $result = array();
            foreach ($mappings as $mappingID => $sectionName) {
                foreach ($settings as $setting) {
                    $kEinstellungenSektion = (int) $setting['kEinstellungenSektion'];
                    if ($kEinstellungenSektion === $mappingID) {
                        if (!isset($result[$sectionName])) {
                            $result[$sectionName] = array();
                        }
                        $result[$sectionName][$setting['cName']] = $setting['cWert'];
                    }
                }
            }
            Shop::Cache()->set($cacheID, $result, array(CACHING_GROUP_TEMPLATE, CACHING_GROUP_OPTION, CACHING_GROUP_CORE));
        }
        $this->_container = $result;

        return $result;
    }

    /**
     * @return array
     */
    private static function getMappings()
    {
        return array(
            CONF_GLOBAL             => 'global',
            CONF_STARTSEITE         => 'startseite',
            CONF_EMAILS             => 'emails',
            CONF_ARTIKELUEBERSICHT  => 'artikeluebersicht',
            CONF_ARTIKELDETAILS     => 'artikeldetails',
            CONF_KUNDEN             => 'kunden',
            CONF_LOGO               => 'logo',
            CONF_KAUFABWICKLUNG     => 'kaufabwicklung',
            CONF_BOXEN              => 'boxen',
            CONF_BILDER             => 'bilder',
            CONF_SONSTIGES          => 'sonstiges',
            CONF_ZAHLUNGSARTEN      => 'zahlungsarten',
            CONF_KONTAKTFORMULAR    => 'kontakt',
            CONF_SHOPINFO           => 'shopinfo',
            CONF_RSS                => 'rss',
            CONF_VERGLEICHSLISTE    => 'vergleichsliste',
            CONF_PREISVERLAUF       => 'preisverlauf',
            CONF_BEWERTUNG          => 'bewertung',
            CONF_NEWSLETTER         => 'newsletter',
            CONF_KUNDENFELD         => 'kundenfeld',
            CONF_NAVIGATIONSFILTER  => 'navigationsfilter',
            CONF_EMAILBLACKLIST     => 'emailblacklist',
            CONF_METAANGABEN        => 'metaangaben',
            CONF_NEWS               => 'news',
            CONF_SITEMAP            => 'sitemap',
            CONF_UMFRAGE            => 'umfrage',
            CONF_KUNDENWERBENKUNDEN => 'kundenwerbenkunden',
            CONF_TRUSTEDSHOPS       => 'trustedshops',
            CONF_SUCHSPECIAL        => 'suchspecials',
            CONF_TEMPLATE           => 'template',
            CONF_PREISANZEIGE       => 'preisanzeige',
            CONF_CHECKBOX           => 'checkbox',
            CONF_AUSWAHLASSISTENT   => 'auswahlassistent',
            CONF_RMA                => 'rma',
            CONF_OBJECTCACHING      => 'objectcaching',
            CONF_CACHING            => 'caching'
        );
    }
}
