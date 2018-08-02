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
    private static $_instance;

    /**
     * @var array
     */
    private $_container = [];

    /**
     * @var array
     */
    private $allSettings;

    /**
     * @var array
     */
    private static $mapping = [
        CONF_GLOBAL              => 'global',
        CONF_STARTSEITE          => 'startseite',
        CONF_EMAILS              => 'emails',
        CONF_ARTIKELUEBERSICHT   => 'artikeluebersicht',
        CONF_ARTIKELDETAILS      => 'artikeldetails',
        CONF_KUNDEN              => 'kunden',
        CONF_LOGO                => 'logo',
        CONF_KAUFABWICKLUNG      => 'kaufabwicklung',
        CONF_BOXEN               => 'boxen',
        CONF_BILDER              => 'bilder',
        CONF_SONSTIGES           => 'sonstiges',
        CONF_ZAHLUNGSARTEN       => 'zahlungsarten',
        CONF_PLUGINZAHLUNGSARTEN => 'pluginzahlungsarten',
        CONF_KONTAKTFORMULAR     => 'kontakt',
        CONF_SHOPINFO            => 'shopinfo',
        CONF_RSS                 => 'rss',
        CONF_VERGLEICHSLISTE     => 'vergleichsliste',
        CONF_PREISVERLAUF        => 'preisverlauf',
        CONF_BEWERTUNG           => 'bewertung',
        CONF_NEWSLETTER          => 'newsletter',
        CONF_KUNDENFELD          => 'kundenfeld',
        CONF_NAVIGATIONSFILTER   => 'navigationsfilter',
        CONF_EMAILBLACKLIST      => 'emailblacklist',
        CONF_METAANGABEN         => 'metaangaben',
        CONF_NEWS                => 'news',
        CONF_SITEMAP             => 'sitemap',
        CONF_UMFRAGE             => 'umfrage',
        CONF_KUNDENWERBENKUNDEN  => 'kundenwerbenkunden',
        CONF_TRUSTEDSHOPS        => 'trustedshops',
        CONF_SUCHSPECIAL         => 'suchspecials',
        CONF_TEMPLATE            => 'template',
        CONF_CHECKBOX            => 'checkbox',
        CONF_AUSWAHLASSISTENT    => 'auswahlassistent',
        CONF_CACHING             => 'caching'
    ];

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
    public static function getInstance(): self
    {
        return self::$_instance ?? new self();
    }

    /**
     * for rare cases when options are modified and directly re-assigned to smarty
     * do not call this function otherwise.
     *
     * @return $this
     */
    public function reset()
    {
        $this->_container = [];

        return $this;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
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
            $section = static::mapSettingName(null, $offset);

            if ($section === false || $section === null) {
                return null;
            }
            $cacheID = 'setting_' . $section;
            // Template work around
            if ($section === CONF_TEMPLATE) {
                if (($templateSettings = Shop::Cache()->get($cacheID)) === false) {
                    $template         = Template::getInstance();
                    $templateSettings = $template->getConfig();
                    Shop::Cache()->set($cacheID, $templateSettings, [CACHING_GROUP_TEMPLATE, CACHING_GROUP_OPTION]);
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
                    Shop::Container()->getLogService()->error('Setting Caching Exception: ' . $exc->getMessage());
                }
                if ($section === CONF_PLUGINZAHLUNGSARTEN) {
                    $settings = Shop::Container()->getDB()->query(
                        "SELECT cName, cWert
                             FROM tplugineinstellungen
                             WHERE cName LIKE '%_min%' 
                              OR cName LIKE '%_max'",
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                     );
                } else {
                    $settings = Shop::Container()->getDB()->queryPrepared(
                        'SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, teinstellungen.cWert,
                            teinstellungenconf.cInputTyp AS type
                            FROM teinstellungen
                            LEFT JOIN teinstellungenconf
                                ON teinstellungenconf.cWertName = teinstellungen.cName
                                AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                            WHERE teinstellungen.kEinstellungenSektion = :section',
                        ['section' => $section],
                        \DB\ReturnType::ARRAY_OF_OBJECTS
                    );
                }
                if (is_array($settings) && count($settings) > 0) {
                    $this->_container[$offset] = [];
                    foreach ($settings as $setting) {
                        if ($setting->type === 'listbox') {
                            if (!isset($this->_container[$offset][$setting->cName])) {
                                $this->_container[$offset][$setting->cName] = [];
                            }
                            $this->_container[$offset][$setting->cName][] = $setting->cWert;
                        } elseif ($setting->type === 'number') {
                            $this->_container[$offset][$setting->cName] = (int)$setting->cWert;
                        } else {
                            $this->_container[$offset][$setting->cName] = $setting->cWert;
                        }
                    }
                    Shop::Cache()->set($cacheID, $settings, [CACHING_GROUP_OPTION]);
                }
            }
        }

        return $this->_container[$offset] ?? null;
    }

    /**
     * @param array|int $sektionen_arr
     * @return array
     */
    public function getSettings($sektionen_arr): array
    {
        $ret = [];
        if (!is_array($sektionen_arr)) {
            $sektionen_arr = (array)$sektionen_arr;
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
     * @param int    $section
     * @param string $option
     * @return string|array|int|null
     */
    public function getValue(int $section, $option)
    {
        $settings    = $this->getSettings([$section]);
        $sectionName = self::mapSettingName($section);

        return $settings[$sectionName][$option] ?? null;
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
        if ($section !== null && isset(self::$mapping[$section])) {
            return self::$mapping[$section];
        }
        if ($name !== null && ($key = array_search($name, self::$mapping, true)) !== false) {
            return $key;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        if ($this->allSettings !== null) {
            return $this->allSettings;
        }
        $settings = Shop::Container()->getDB()->query(
            'SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, teinstellungen.cWert,
                teinstellungenconf.cInputTyp AS type
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                ORDER BY kEinstellungenSektion',
            \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS
        );
        $result = [];
        foreach (self::$mapping as $mappingID => $sectionName) {
            foreach ($settings as $setting) {
                $kEinstellungenSektion = (int)$setting['kEinstellungenSektion'];
                if ($kEinstellungenSektion === $mappingID) {
                    if (!isset($result[$sectionName])) {
                        $result[$sectionName] = [];
                    }
                    if ($setting['type'] === 'listbox') {
                        if (!isset($result[$sectionName][$setting['cName']])) {
                            $result[$sectionName][$setting['cName']] = [];
                        }
                        $result[$sectionName][$setting['cName']][] = $setting['cWert'];
                    } elseif ($setting['type'] === 'number') {
                        $result[$sectionName][$setting['cName']] = (int)$setting['cWert'];
                    } else {
                        $result[$sectionName][$setting['cName']] = $setting['cWert'];
                    }
                }
            }
        }
        $template           = Template::getInstance();
        $result['template'] = $template->getConfig();
        $this->allSettings  = $result;

        return $result;
    }

    /**
     * preload the _container variable with one single sql statement or one single cache call
     * this is being called after successful cache initialisation in class.JTL-Shop.JTLCache.php
     *
     * @return array
     */
    public function preLoad(): array
    {
        $cacheID = 'settings_all_preload';
        if (($result = Shop::Cache()->get($cacheID)) === false) {
            $result = $this->getAll();
            Shop::Cache()->set($cacheID, $result, [CACHING_GROUP_TEMPLATE, CACHING_GROUP_OPTION, CACHING_GROUP_CORE]);
        }
        $this->_container  = $result;
        $this->allSettings = $result;

        return $result;
    }

    /**
     * @return string[]
     */
    private static function getMappings(): array
    {
        return self::$mapping;
    }
}
