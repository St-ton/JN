<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Trennzeichen
 */
class Trennzeichen
{
    /**
     * @var int
     */
    public $kTrennzeichen;

    /**
     * @var int
     */
    protected $kSprache;

    /**
     * @var int
     */
    protected $nEinheit;

    /**
     * @var int
     */
    protected $nDezimalstellen;

    /**
     * @var string
     */
    protected $cDezimalZeichen;

    /**
     * @var string
     */
    protected $cTausenderZeichen;

    /**
     * @var array
     */
    private static $unitObject = [];

    /**
     * Trennzeichen constructor.
     *
     * @param int $kTrennzeichen
     */
    public function __construct(int $kTrennzeichen = 0)
    {
        if ($kTrennzeichen > 0) {
            $this->loadFromDB($kTrennzeichen);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $kTrennzeichen
     * @return $this
     */
    private function loadFromDB(int $kTrennzeichen = 0): self
    {
        $cacheID = 'units_lfdb_' . $kTrennzeichen;
        if (($oObj = Shop::Cache()->get($cacheID)) === false) {
            $oObj = Shop::Container()->getDB()->select('ttrennzeichen', 'kTrennzeichen', $kTrennzeichen);
            Shop::Cache()->set($cacheID, $oObj, [CACHING_GROUP_CORE]);
        }
        if (isset($oObj->kTrennzeichen) && $oObj->kTrennzeichen > 0) {
            $cMember_arr = array_keys(get_object_vars($oObj));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oObj->$cMember;
            }
        }

        return $this;
    }

    /**
     * getUnit() can be called very often within one page request
     * so try to use static class variable and object cache to avoid
     * unnecessary sql request
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @return mixed
     */
    private static function getUnitObject(int $nEinheit, int $kSprache)
    {
        if (isset(self::$unitObject[$kSprache][$nEinheit])) {
            return self::$unitObject[$kSprache][$nEinheit];
        }
        $cacheID = 'units_' . $nEinheit . '_' . $kSprache;
        if (($oObj = Shop::Cache()->get($cacheID)) === false) {
            $oObj = Shop::Container()->getDB()->select(
                'ttrennzeichen',
                'nEinheit', $nEinheit,
                'kSprache', $kSprache
            );
            Shop::Cache()->set($cacheID, $oObj, [CACHING_GROUP_CORE]);
        }
        if (!isset(self::$unitObject[$kSprache])) {
            self::$unitObject[$kSprache] = [];
        }
        self::$unitObject[$kSprache][$nEinheit] = $oObj;

        return $oObj;
    }

    /**
     * Loads database member into class member
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @param int $fAmount
     * @return int|string|Trennzeichen
     */
    public static function getUnit(int $nEinheit, int $kSprache, $fAmount = -1)
    {
        if (!$kSprache) {
            $oSprache = gibStandardsprache(true);
            $kSprache = (int)$oSprache->kSprache;
        }

        if ($nEinheit > 0 && $kSprache > 0) {
            $oObj = self::getUnitObject($nEinheit, $kSprache);
            if ($oObj === null && self::insertMissingRow($nEinheit, $kSprache) === 1) {
                $oObj = self::getUnitObject($nEinheit, $kSprache);
            }
            if (isset($oObj->kTrennzeichen) && $oObj->kTrennzeichen > 0) {
                return $fAmount >= 0
                    ? number_format($fAmount, $oObj->nDezimalstellen, $oObj->cDezimalZeichen, $oObj->cTausenderZeichen)
                    : new self($oObj->kTrennzeichen);
            }
        }

        return $fAmount;
    }

    /**
     * Insert missing trennzeichen
     *
     * @param int $nEinheit
     * @param int $kSprache
     * @return mixed|bool
     */
    public static function insertMissingRow(int $nEinheit, int $kSprache)
    {
        // Standardwert [kSprache][nEinheit]
        $xRowAssoc_arr = [];
        foreach (gibAlleSprachen() as $language) {
            $xRowAssoc_arr[$language->kSprache][JTL_SEPARATOR_WEIGHT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $xRowAssoc_arr[$language->kSprache][JTL_SEPARATOR_LENGTH] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $xRowAssoc_arr[$language->kSprache][JTL_SEPARATOR_AMOUNT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
        }
        if ($nEinheit > 0 && $kSprache > 0) {
            if (!isset($xRowAssoc_arr[$kSprache][$nEinheit])) {
                $xRowAssoc_arr[$kSprache]            = [];
                $xRowAssoc_arr[$kSprache][$nEinheit] = [
                    'nDezimalstellen'   => 2,
                    'cDezimalZeichen'   => ',',
                    'cTausenderZeichen' => '.'
                ];
            }
            Shop::Cache()->flushTags([CACHING_GROUP_CORE]);

            return Shop::Container()->getDB()->query(
                "INSERT INTO `ttrennzeichen` 
                    (`kTrennzeichen`, `kSprache`, `nEinheit`, `nDezimalstellen`, `cDezimalZeichen`, `cTausenderZeichen`)
                    VALUES (
                      NULL, {$kSprache}, {$nEinheit}, {$xRowAssoc_arr[$kSprache][$nEinheit]['nDezimalstellen']}, 
                      '{$xRowAssoc_arr[$kSprache][$nEinheit]['cDezimalZeichen']}',
                    '{$xRowAssoc_arr[$kSprache][$nEinheit]['cTausenderZeichen']}')",
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }

    /**
     * @param int $kSprache
     * @return array
     */
    public static function getAll(int $kSprache)
    {
        $cacheID  = 'units_all_' . $kSprache;
        if (($oObjAssoc_arr = Shop::Cache()->get($cacheID)) === false) {
            $oObjAssoc_arr = [];
            if ($kSprache > 0) {
                $oObjTMP_arr = Shop::Container()->getDB()->selectAll(
                    'ttrennzeichen',
                    'kSprache',
                    $kSprache,
                    'kTrennzeichen',
                    'nEinheit'
                );
                foreach ($oObjTMP_arr as $oObjTMP) {
                    if (isset($oObjTMP->kTrennzeichen) && $oObjTMP->kTrennzeichen > 0) {
                        $oTrennzeichen                               = new self($oObjTMP->kTrennzeichen);
                        $oObjAssoc_arr[$oTrennzeichen->getEinheit()] = $oTrennzeichen;
                    }
                }
            }
            Shop::Cache()->set($cacheID, $oObjAssoc_arr, [CACHING_GROUP_CORE]);
        }

        return $oObjAssoc_arr;
    }

    /**
     * @param bool $bPrim - Controls the return of the method
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $oObj->$cMember = $this->$cMember;
            }
        }
        unset($oObj->kTrennzeichen);

        $kPrim = Shop::Container()->getDB()->insert('ttrennzeichen', $oObj);

        if ($kPrim > 0) {
            return $bPrim ? $kPrim : true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $upd                    = new stdClass();
        $upd->kSprache          = (int)$this->kSprache;
        $upd->nEinheit          = $this->nEinheit;
        $upd->nDezimalstellen   = $this->nDezimalstellen;
        $upd->cDezimalZeichen   = $this->cDezimalZeichen;
        $upd->cTausenderZeichen = $this->cTausenderZeichen;

        return Shop::Container()->getDB()->update('ttrennzeichen', 'kTrennzeichen', $this->kTrennzeichen, $upd);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('ttrennzeichen', 'kTrennzeichen', $this->kTrennzeichen);
    }

    /**
     * @param int $kTrennzeichen
     * @return $this
     */
    public function setTrennzeichen(int $kTrennzeichen): self
    {
        $this->kTrennzeichen = $kTrennzeichen;

        return $this;
    }

    /**
     * @param int $kSprache
     * @return $this
     */
    public function setSprache(int $kSprache): self
    {
        $this->kSprache = $kSprache;

        return $this;
    }

    /**
     * @param int $nEinheit
     * @return $this
     */
    public function setEinheit(int $nEinheit): self
    {
        $this->nEinheit = $nEinheit;

        return $this;
    }

    /**
     * @param int $nDezimalstellen
     * @return $this
     */
    public function setDezimalstellen(int $nDezimalstellen): self
    {
        $this->nDezimalstellen = $nDezimalstellen;

        return $this;
    }

    /**
     * @param string $cDezimalZeichen
     * @return $this
     */
    public function setDezimalZeichen($cDezimalZeichen): self
    {
        $this->cDezimalZeichen = $cDezimalZeichen;

        return $this;
    }

    /**
     * @param string $cTausenderZeichen
     * @return $this
     */
    public function setTausenderZeichen($cTausenderZeichen): self
    {
        $this->cTausenderZeichen = $cTausenderZeichen;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrennzeichen()
    {
        return $this->kTrennzeichen;
    }

    /**
     * @return int
     */
    public function getSprache()
    {
        return $this->kSprache;
    }

    /**
     * @return int
     */
    public function getEinheit()
    {
        return $this->nEinheit;
    }

    /**
     * @return int
     */
    public function getDezimalstellen()
    {
        return $this->nDezimalstellen;
    }

    /**
     * @return string
     */
    public function getDezimalZeichen()
    {
        return htmlentities($this->cDezimalZeichen);
    }

    /**
     * @return string
     */
    public function getTausenderZeichen()
    {
        return htmlentities($this->cTausenderZeichen);
    }

    /**
     * @return int|bool
     */
    public static function migrateUpdate()
    {
        $conf      = Shop::getSettings([CONF_ARTIKELDETAILS, CONF_ARTIKELUEBERSICHT]);
        $languages = gibAlleSprachen();
        if (is_array($languages) && count($languages) > 0) {
            Shop::Container()->getDB()->query('TRUNCATE ttrennzeichen', \DB\ReturnType::AFFECTED_ROWS);
            $units = [JTL_SEPARATOR_WEIGHT, JTL_SEPARATOR_AMOUNT, JTL_SEPARATOR_LENGTH];
            foreach ($languages as $language) {
                foreach ($units as $unit) {
                    $sep = new self();
                    if ($unit === JTL_SEPARATOR_WEIGHT) {
                        $dec = isset($conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl'])
                        && strlen($conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl']) > 0
                            ? $conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl']
                            : 2;
                        $sep->setDezimalstellen($dec);
                    } else {
                        $sep->setDezimalstellen(2);
                    }
                    $sep10   = isset($conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner'])
                    && strlen($conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']) > 0
                        ? $conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']
                        : ',';
                    $sep1000 = isset($conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner'])
                    && strlen($conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner']) > 0
                        ? $conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner']
                        : '.';
                    $sep->setDezimalZeichen($sep10)
                        ->setTausenderZeichen($sep1000)
                        ->setSprache($language->kSprache)
                        ->setEinheit($unit)
                        ->save();
                }
            }
            Shop::Cache()->flushTags([CACHING_GROUP_CORE]);

            return Shop::Container()->getDB()->query(
                "DELETE teinstellungen, teinstellungenconf
                    FROM teinstellungenconf
                    LEFT JOIN teinstellungen 
                        ON teinstellungen.cName = teinstellungenconf.cWertName
                    WHERE teinstellungenconf.kEinstellungenConf IN (1458, 1459, 495, 497, 499, 501)",
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }
}
