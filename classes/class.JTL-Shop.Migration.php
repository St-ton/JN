<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Migration
 */
class Migration implements JsonSerializable
{
    /**
     * @var string
     */
    protected $info;

    /**
     * @var DateTime
     */
    protected $created;

    /**
     * Migration constructor.
     *
     * @param null|string   $info
     * @param DateTime|null $created
     */
    public function __construct($info = null, DateTime $created = null)
    {
        $this->info    = ucfirst(strtolower($info));
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return (isset($this->author) && $this->author !== null)
            ? $this->author : null;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->info;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'author'      => $this->getAuthor(),
            'description' => $this->getDescription(),
            'created'     => $this->getCreated()
        ];
    }

    /**
     * executes query and returns misc data
     *
     * @access public
     * @param string   $query - Statement to be executed
     * @param int      $return - what should be returned.
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws InvalidArgumentException
     */
    protected function __execute($query, $return, $echo = false, $bExecuteHook = false)
    {
        if (JTL_CHARSET === 'iso-8859-1') {
            $query = utf8_convert_recursive($query, false);
        }

        return Shop::DB()->executeQuery($query, $return, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function execute($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, 3, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchOne($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, 1, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchAll($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, 2, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchArray($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, 9, $echo, $bExecuteHook);
    }

    /**
     * @return array
     */
    public function getLocaleSections()
    {
        $result = [];
        $items  = Shop::DB()->executeQuery("SELECT kSprachsektion as id, cName as name FROM tsprachsektion", 2);
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        $result = [];
        $items  = Shop::DB()->executeQuery("SELECT kSprachISO as id, cISO as name FROM tsprachiso", 2);
        foreach ($items as $item) {
            $result[$item->name] = $item->id;
        }

        return $result;
    }

    /**
     * @param $table
     * @param $column
     */
    public function dropColumn($table, $column)
    {
        try {
            Shop::DB()->executeQuery("ALTER TABLE `{$table}` DROP `{$column}`", 3);
        } catch (Exception $e) {
        }
    }

    /**
     * Add or update a row in tsprachwerte 
     * 
     * @param string $locale locale iso code e.g. "ger"
     * @param string $section section e.g. "global". See tsprachsektion for all sections
     * @param string $key unique name to identify localization
     * @param string $value localized text
     * @param bool   $system optional flag for system-default.
     * @throws Exception if locale key or section is wrong
     */
    public function setLocalization($locale, $section, $key, $value, $system = true)
    {
        $locales  = $this->getLocales();
        $sections = $this->getLocaleSections();

        if (!isset($locales[$locale])) {
            throw new Exception("Locale key '{$locale}' not found");
        }

        if (!isset($sections[$section])) {
            throw new Exception("section name '{$section}' not found");
        }

        Shop::DB()->executeQuery("insert into tsprachwerte set
            kSprachISO = '{$locales[$locale]}', 
            kSprachsektion = '{$sections[$section]}', 
            cName = '{$key}', 
            cWert = '{$value}', 
            cStandard = '{$value}', 
            bSystem = '{$system}' 
            on duplicate key update cStandard=VALUES(cStandard), cWert=IF(cWert = cStandard, VALUES(cStandard), cWert)", 3);
    }

    /**
     * @param string $key
     */
    public function removeLocalization($key)
    {
        Shop::DB()->executeQuery("DELETE FROM tsprachwerte WHERE cName='{$key}'", 3);
    }

    /**
     * @return array
     */
    private function getAvailableInputTypes()
    {
        $result = [];
        $items  = $this->fetchAll("SELECT DISTINCT cInputTyp FROM `teinstellungenconf` WHERE cInputTyp IS NOT NULL AND cInputTyp!=''");
        foreach ($items as $item) {
            $result[] = $item->cInputTyp;
        }

        return $result;
    }

    /**
     * @param string $table
     * @param string $column
     * @return mixed
     */
    private function getLastId($table, $column)
    {
        $result = $this->fetchOne(" SELECT `$column` as last_id FROM `$table` ORDER BY `$column` DESC LIMIT 1");

        return ++$result->last_id;
    }

    /**
     * @param string $configName internal config name
     * @param string $configValue default config value
     * @param int $configSection config section
     * @param string $externalName displayed config name
     * @param string $inputType config input type (set to NULL and set additionalProperties->cConf to "N" for section header)
     * @param int $sort internal sorting number
     * @param array|null $additionalProperties
     * @param bool $overwrite force overwrite of already existing config
     * @throws Exception
     */
    public function setConfig(
        $configName,
        $configValue,
        $configSection,
        $externalName,
        $inputType,
        $sort,
        $additionalProperties = null,
        $overwrite = false
    ) {
        $availableInputTypes = $this->getAvailableInputTypes();

        //input types that need $additionalProperties->inputOptions
        $inputTypeNeedsOptions = array('listbox', 'selectbox');

        $kEinstellungenConf = (!is_object($additionalProperties) || !isset($additionalProperties->kEinstellungenConf) || !$additionalProperties->kEinstellungenConf) ?
            $this->getLastId('teinstellungenconf', 'kEinstellungenConf') : $additionalProperties->kEinstellungenConf;
        if (!$configName) {
            throw new Exception('configName not provided or empty / zero');
        } elseif (!$configSection) {
            throw new Exception('configSection not provided or empty / zero');
        } elseif (!$externalName) {
            throw new Exception('externalName not provided or empty / zero');
        } elseif (!$sort) {
            throw new Exception('sort not provided or empty / zero');
        } elseif (!$inputType && (!is_object($additionalProperties) || !isset($additionalProperties->cConf) || $additionalProperties->cConf != 'N')) {
            throw new Exception('inputType has to be provided if additionalProperties->cConf is not set to "N"');
        } elseif (in_array($inputType, $inputTypeNeedsOptions) &&
            (!is_object($additionalProperties) || !isset($additionalProperties->inputOptions) || !is_array($additionalProperties->inputOptions) || count($additionalProperties->inputOptions) == 0)
        ) {
            throw new Exception('additionalProperties->inputOptions has to be provided if inputType is "' . $inputType . '"');
        } elseif ($overwrite !== true) {
            $count = $this->fetchOne("SELECT COUNT(*) as count FROM teinstellungen WHERE cName='{$configName}'");
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungen and overwrite is disabled');
            }
            $count = $this->fetchOne("SELECT COUNT(*) as count FROM teinstellungenconf WHERE cWertName='{$configName}' OR kEinstellungenConf={$kEinstellungenConf}");
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungenconf and overwrite is disabled');
            }
            $count = $this->fetchOne("SELECT COUNT(*) as count FROM teinstellungenconfwerte WHERE kEinstellungenConf={$kEinstellungenConf}");
            if ($count->count != 0) {
                throw new Exception('another entry already present in teinstellungenconfwerte and overwrite is disabled');
            }

            unset($count);

            // $overwrite has to be set to true in order to create a new inputType
            if (!in_array($inputType,
                    $availableInputTypes) && (!is_object($additionalProperties) || !isset($additionalProperties->cConf) || $additionalProperties->cConf != 'N')
            ) {
                throw new Exception('inputType "' . $inputType . '" not in available types and additionalProperties->cConf is not set to "N"');
            }
        }
        $this->removeConfig($configName);

        $cConf             = (!is_object($additionalProperties) || !isset($additionalProperties->cConf) || $additionalProperties->cConf != 'N') ? 'Y' : 'N';
        $inputType         = $cConf === 'N' ? '' : $inputType;
        $cModulId          = (!is_object($additionalProperties) || !isset($additionalProperties->cModulId)) ? '_DBNULL_' : $additionalProperties->cModulId;
        $cBeschreibung     = (!is_object($additionalProperties) || !isset($additionalProperties->cBeschreibung)) ? '' : $additionalProperties->cBeschreibung;
        $nStandardAnzeigen = (!is_object($additionalProperties) || !isset($additionalProperties->nStandardAnzeigen)) ? 1 : $additionalProperties->nStandardAnzeigen;
        $nModul            = (!is_object($additionalProperties) || !isset($additionalProperties->nModul)) ? 0 : $additionalProperties->nModul;

        $einstellungen                        = new stdClass();
        $einstellungen->kEinstellungenSektion = $configSection;
        $einstellungen->cName                 = $configName;
        $einstellungen->cWert                 = $configValue;
        $einstellungen->cModulId              = $cModulId;
        Shop::DB()->insertRow('teinstellungen', $einstellungen, true);
        unset($einstellungen);

        $einstellungenConf                        = new stdClass();
        $einstellungenConf->kEinstellungenConf    = $kEinstellungenConf;
        $einstellungenConf->kEinstellungenSektion = $configSection;
        $einstellungenConf->cName                 = $externalName;
        $einstellungenConf->cBeschreibung         = $cBeschreibung;
        $einstellungenConf->cWertName             = $configName;
        $einstellungenConf->cInputTyp             = $inputType;
        $einstellungenConf->cModulId              = $cModulId;
        $einstellungenConf->nSort                 = $sort;
        $einstellungenConf->nStandardAnzeigen     = $nStandardAnzeigen;
        $einstellungenConf->nModul                = $nModul;
        $einstellungenConf->cConf                 = $cConf;
        Shop::DB()->insertRow('teinstellungenconf', $einstellungenConf, true);
        unset($einstellungenConf);

        if (is_object($additionalProperties) && isset($additionalProperties->inputOptions) && is_array($additionalProperties->inputOptions)) {
            $sortIndex              = 1;
            $einstellungenConfWerte = new stdClass();
            foreach ($additionalProperties->inputOptions as $optionKey => $optionValue) {
                $einstellungenConfWerte->kEinstellungenConf = $kEinstellungenConf;
                $einstellungenConfWerte->cName              = $optionValue;
                $einstellungenConfWerte->cWert              = $optionKey;
                $einstellungenConfWerte->nSort              = $sortIndex;
                Shop::DB()->insertRow('teinstellungenconfwerte', $einstellungenConfWerte, true);
                $sortIndex++;
            }
            unset($einstellungenConfWerte);
        }
    }

    /**
     * @param string $key the key name to be removed
     */
    public function removeConfig($key)
    {
        $this->execute("DELETE FROM teinstellungen WHERE cName='{$key}'");
        $this->execute("DELETE FROM teinstellungenconfwerte WHERE kEinstellungenConf= (SELECT kEinstellungenConf FROM teinstellungenconf WHERE cWertName='{$key}')");
        $this->execute("DELETE FROM teinstellungenconf WHERE cWertName='{$key}'");
    }
}
