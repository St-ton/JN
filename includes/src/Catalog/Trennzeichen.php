<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Catalog;

use JTL\DB\ReturnType;
use JTL\Shop;
use JTL\Sprache;
use stdClass;

/**
 * Class Trennzeichen
 * @package JTL\Catalog
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
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->select('ttrennzeichen', 'kTrennzeichen', $kTrennzeichen);
            Shop::Container()->getCache()->set($cacheID, $data, [\CACHING_GROUP_CORE]);
        }
        if (isset($data->kTrennzeichen) && $data->kTrennzeichen > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
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
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->select(
                'ttrennzeichen',
                'nEinheit',
                $nEinheit,
                'kSprache',
                $kSprache
            );
            if ($data !== null) {
                $data->kTrennzeichen   = (int)$data->kTrennzeichen;
                $data->kSprache        = (int)$data->kSprache;
                $data->nEinheit        = (int)$data->nEinheit;
                $data->nDezimalstellen = (int)$data->nDezimalstellen;
            }

            Shop::Container()->getCache()->set($cacheID, $data, [\CACHING_GROUP_CORE]);
        }
        if (!isset(self::$unitObject[$kSprache])) {
            self::$unitObject[$kSprache] = [];
        }
        self::$unitObject[$kSprache][$nEinheit] = $data;

        return $data;
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
            $oSprache = Sprache::getDefaultLanguage();
            $kSprache = (int)$oSprache->kSprache;
        }

        if ($nEinheit > 0 && $kSprache > 0) {
            $data = self::getUnitObject($nEinheit, $kSprache);
            if ($data === null && self::insertMissingRow($nEinheit, $kSprache) === 1) {
                $data = self::getUnitObject($nEinheit, $kSprache);
            }
            if (isset($data->kTrennzeichen) && $data->kTrennzeichen > 0) {
                return $fAmount >= 0
                    ? \number_format(
                        (float)$fAmount,
                        $data->nDezimalstellen,
                        $data->cDezimalZeichen,
                        $data->cTausenderZeichen
                    )
                    : new self($data->kTrennzeichen);
            }
        }

        return $fAmount;
    }

    /**
     * Insert missing trennzeichen
     *
     * @param int $unit
     * @param int $languageID
     * @return mixed|bool
     */
    public static function insertMissingRow(int $unit, int $languageID)
    {
        // Standardwert [kSprache][nEinheit]
        $rows = [];
        foreach (Sprache::getAllLanguages() as $language) {
            $rows[$language->kSprache][\JTL_SEPARATOR_WEIGHT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $rows[$language->kSprache][\JTL_SEPARATOR_LENGTH] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $rows[$language->kSprache][\JTL_SEPARATOR_AMOUNT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
        }
        if ($unit > 0 && $languageID > 0) {
            if (!isset($rows[$languageID][$unit])) {
                $rows[$languageID]        = [];
                $rows[$languageID][$unit] = [
                    'nDezimalstellen'   => 2,
                    'cDezimalZeichen'   => ',',
                    'cTausenderZeichen' => '.'
                ];
            }
            Shop::Container()->getCache()->flushTags([\CACHING_GROUP_CORE]);

            return Shop::Container()->getDB()->query(
                "INSERT INTO `ttrennzeichen` 
                    (`kTrennzeichen`, `kSprache`, `nEinheit`, `nDezimalstellen`, `cDezimalZeichen`, `cTausenderZeichen`)
                    VALUES (
                      NULL, {$languageID}, {$unit}, {$rows[$languageID][$unit]['nDezimalstellen']}, 
                      '{$rows[$languageID][$unit]['cDezimalZeichen']}',
                    '{$rows[$languageID][$unit]['cTausenderZeichen']}')",
                ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }

    /**
     * @param int $kSprache
     * @return array
     */
    public static function getAll(int $kSprache): array
    {
        $cacheID = 'units_all_' . $kSprache;
        if (($all = Shop::Container()->getCache()->get($cacheID)) === false) {
            $all = [];
            if ($kSprache > 0) {
                $data = Shop::Container()->getDB()->selectAll(
                    'ttrennzeichen',
                    'kSprache',
                    $kSprache,
                    'kTrennzeichen',
                    'nEinheit'
                );
                foreach ($data as $item) {
                    $oTrennzeichen                     = new self($item->kTrennzeichen);
                    $all[$oTrennzeichen->getEinheit()] = $oTrennzeichen;
                }
            }
            Shop::Container()->getCache()->set($cacheID, $all, [\CACHING_GROUP_CORE]);
        }

        return $all;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $data = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $data->$member = $this->$member;
        }
        unset($data->kTrennzeichen);

        $kPrim = Shop::Container()->getDB()->insert('ttrennzeichen', $data);

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
        $upd->nEinheit          = (int)$this->nEinheit;
        $upd->nDezimalstellen   = (int)$this->nDezimalstellen;
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
     * @return int|null
     */
    public function getTrennzeichen(): ?int
    {
        return $this->kTrennzeichen;
    }

    /**
     * @return int|null
     */
    public function getSprache(): ?int
    {
        return $this->kSprache;
    }

    /**
     * @return int|null
     */
    public function getEinheit(): ?int
    {
        return $this->nEinheit;
    }

    /**
     * @return int|null
     */
    public function getDezimalstellen(): ?int
    {
        return $this->nDezimalstellen;
    }

    /**
     * @return string
     */
    public function getDezimalZeichen(): string
    {
        return \htmlentities($this->cDezimalZeichen);
    }

    /**
     * @return string
     */
    public function getTausenderZeichen(): string
    {
        return \htmlentities($this->cTausenderZeichen);
    }

    /**
     * @return int|bool
     */
    public static function migrateUpdate()
    {
        $conf      = Shop::getSettings([\CONF_ARTIKELDETAILS, \CONF_ARTIKELUEBERSICHT]);
        $languages = Sprache::getAllLanguages();
        if (\is_array($languages) && \count($languages) > 0) {
            Shop::Container()->getDB()->query('TRUNCATE ttrennzeichen', ReturnType::AFFECTED_ROWS);
            $units = [\JTL_SEPARATOR_WEIGHT, \JTL_SEPARATOR_AMOUNT, \JTL_SEPARATOR_LENGTH];
            foreach ($languages as $language) {
                foreach ($units as $unit) {
                    $sep = new self();
                    if ($unit === \JTL_SEPARATOR_WEIGHT) {
                        $dec = isset($conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl'])
                        && \mb_strlen($conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl']) > 0
                            ? $conf['artikeldetails']['artikeldetails_gewicht_stellenanzahl']
                            : 2;
                        $sep->setDezimalstellen($dec);
                    } else {
                        $sep->setDezimalstellen(2);
                    }
                    $sep10   = isset($conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner'])
                    && \mb_strlen($conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']) > 0
                        ? $conf['artikeldetails']['artikeldetails_zeichen_nachkommatrenner']
                        : ',';
                    $sep1000 = isset($conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner'])
                    && \mb_strlen($conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner']) > 0
                        ? $conf['artikeldetails']['artikeldetails_zeichen_tausendertrenner']
                        : '.';
                    $sep->setDezimalZeichen($sep10)
                        ->setTausenderZeichen($sep1000)
                        ->setSprache($language->kSprache)
                        ->setEinheit($unit)
                        ->save();
                }
            }
            Shop::Container()->getCache()->flushTags([\CACHING_GROUP_CORE]);

            return Shop::Container()->getDB()->query(
                'DELETE teinstellungen, teinstellungenconf
                    FROM teinstellungenconf
                    LEFT JOIN teinstellungen 
                        ON teinstellungen.cName = teinstellungenconf.cWertName
                    WHERE teinstellungenconf.kEinstellungenConf IN (1458, 1459, 495, 497, 499, 501)',
                ReturnType::AFFECTED_ROWS
            );
        }

        return false;
    }
}
