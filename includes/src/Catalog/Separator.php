<?php declare(strict_types=1);

namespace JTL\Catalog;

use JTL\Language\LanguageHelper;
use JTL\Shop;
use stdClass;

/**
 * Class Separator
 * @package JTL\Catalog
 */
class Separator
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
    private static array $unitObject = [];

    /**
     * Separator constructor.
     * @param int $id
     */
    public function __construct(int $id = 0)
    {
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int $id
     * @return $this
     */
    private function loadFromDB(int $id = 0): self
    {
        $cacheID = 'units_lfdb_' . $id;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->select('ttrennzeichen', 'kTrennzeichen', $id);
            Shop::Container()->getCache()->set($cacheID, $data, [\CACHING_GROUP_CORE]);
        }
        if (isset($data->kTrennzeichen) && $data->kTrennzeichen > 0) {
            $this->kTrennzeichen     = (int)$data->kTrennzeichen;
            $this->kSprache          = (int)$data->kSprache;
            $this->nEinheit          = (int)$data->nEinheit;
            $this->nDezimalstellen   = (int)$data->nDezimalstellen;
            $this->cDezimalZeichen   = $data->cDezimalZeichen;
            $this->cTausenderZeichen = $data->cTausenderZeichen;
        }

        return $this;
    }

    /**
     * getUnit() can be called very often within one page request
     * so try to use static class variable and object cache to avoid
     * unnecessary sql request
     *
     * @param int $unitID
     * @param int $languageID
     * @return mixed
     */
    private static function getUnitObject(int $unitID, int $languageID)
    {
        if (isset(self::$unitObject[$languageID][$unitID])) {
            return self::$unitObject[$languageID][$unitID];
        }
        $cacheID = 'units_' . $unitID . '_' . $languageID;
        if (($data = Shop::Container()->getCache()->get($cacheID)) === false) {
            $data = Shop::Container()->getDB()->select(
                'ttrennzeichen',
                'nEinheit',
                $unitID,
                'kSprache',
                $languageID
            );
            if ($data !== null) {
                $data->kTrennzeichen   = (int)$data->kTrennzeichen;
                $data->kSprache        = (int)$data->kSprache;
                $data->nEinheit        = (int)$data->nEinheit;
                $data->nDezimalstellen = (int)$data->nDezimalstellen;
            }

            Shop::Container()->getCache()->set($cacheID, $data, [\CACHING_GROUP_CORE]);
        }
        if (!isset(self::$unitObject[$languageID])) {
            self::$unitObject[$languageID] = [];
        }
        self::$unitObject[$languageID][$unitID] = $data;

        return $data;
    }

    /**
     * Loads database member into class member
     *
     * @param int       $unitID
     * @param int       $languageID
     * @param int|float $qty
     * @return int|string|Separator
     */
    public static function getUnit(int $unitID, int $languageID, $qty = -1)
    {
        if (!$languageID) {
            $languageID = LanguageHelper::getDefaultLanguage()->getId();
        }
        if ($unitID > 0 && $languageID > 0) {
            $data = self::getUnitObject($unitID, $languageID);
            if ($data === null && self::insertMissingRow($unitID, $languageID) === 1) {
                $data = self::getUnitObject($unitID, $languageID);
            }
            if (isset($data->kTrennzeichen) && $data->kTrennzeichen > 0) {
                return $qty >= 0
                    ? \number_format(
                        (float)$qty,
                        $data->nDezimalstellen,
                        $data->cDezimalZeichen,
                        $data->cTausenderZeichen
                    )
                    : new self($data->kTrennzeichen);
            }
        }

        return $qty;
    }

    /**
     * Insert missing trennzeichen
     *
     * @param int $unitID
     * @param int $languageID
     * @return int|bool
     */
    public static function insertMissingRow(int $unitID, int $languageID)
    {
        // Standardwert [kSprache][nEinheit]
        $rows = [];
        foreach (LanguageHelper::getAllLanguages() as $language) {
            $rows[$language->getId()][\JTL_SEPARATOR_WEIGHT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $rows[$language->getId()][\JTL_SEPARATOR_LENGTH] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
            $rows[$language->getId()][\JTL_SEPARATOR_AMOUNT] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
        }
        if ($unitID <= 0 || $languageID <= 0) {
            return false;
        }
        if (!isset($rows[$languageID][$unitID])) {
            $rows[$languageID]          = [];
            $rows[$languageID][$unitID] = [
                'nDezimalstellen'   => 2,
                'cDezimalZeichen'   => ',',
                'cTausenderZeichen' => '.'
            ];
        }
        $ins                    = new stdClass();
        $ins->kSprache          = $languageID;
        $ins->nEinheit          = $unitID;
        $ins->nDezimalstellen   = $rows[$languageID][$unitID]['nDezimalstellen'];
        $ins->cDezimalZeichen   = $rows[$languageID][$unitID]['cDezimalZeichen'];
        $ins->cTausenderZeichen = $rows[$languageID][$unitID]['cTausenderZeichen'];

        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_CORE]);

        return Shop::Container()->getDB()->insert('ttrennzeichen', $ins);
    }

    /**
     * @param int $languageID
     * @return array
     */
    public static function getAll(int $languageID): array
    {
        $cacheID = 'units_all_' . $languageID;
        if (($all = Shop::Container()->getCache()->get($cacheID)) === false) {
            $all = [];
            if ($languageID > 0) {
                $data = Shop::Container()->getDB()->selectAll(
                    'ttrennzeichen',
                    'kSprache',
                    $languageID,
                    'kTrennzeichen',
                    'nEinheit'
                );
                foreach ($data as $item) {
                    $sep                     = new self((int)$item->kTrennzeichen);
                    $all[$sep->getEinheit()] = $sep;
                }
            }
            Shop::Container()->getCache()->set($cacheID, $all, [\CACHING_GROUP_CORE]);
        }

        return $all;
    }

    /**
     * @param bool $primary
     * @return bool|int
     */
    public function save(bool $primary = true)
    {
        $data = new stdClass();
        foreach (\array_keys(\get_object_vars($this)) as $member) {
            $data->$member = $this->$member;
        }
        unset($data->kTrennzeichen);

        $id = Shop::Container()->getDB()->insert('ttrennzeichen', $data);

        if ($id > 0) {
            return $primary ? $id : true;
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
     * @param int $languageID
     * @return $this
     */
    public function setSprache(int $languageID): self
    {
        $this->kSprache = $languageID;

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
        $conf      = Shop::getSettingSection(\CONF_ARTIKELDETAILS);
        $languages = LanguageHelper::getAllLanguages();
        if (\is_array($languages) && \count($languages) > 0) {
            Shop::Container()->getDB()->query('TRUNCATE ttrennzeichen');
            $units = [\JTL_SEPARATOR_WEIGHT, \JTL_SEPARATOR_AMOUNT, \JTL_SEPARATOR_LENGTH];
            foreach ($languages as $language) {
                foreach ($units as $unit) {
                    $sep = new self();
                    $dec = 2;
                    if ($unit === \JTL_SEPARATOR_WEIGHT) {
                        $dec = ($conf['artikeldetails_gewicht_stellenanzahl'] ?? '') !== ''
                            ? (int)$conf['artikeldetails_gewicht_stellenanzahl']
                            : 2;
                    }
                    $sep10   = ($conf['artikeldetails_zeichen_nachkommatrenner'] ?? '') !== ''
                        ? $conf['artikeldetails_zeichen_nachkommatrenner']
                        : ',';
                    $sep1000 = ($conf['artikeldetails_zeichen_tausendertrenner'] ?? '') !== ''
                        ? $conf['artikeldetails_zeichen_tausendertrenner']
                        : '.';
                    $sep->setDezimalstellen($dec)
                        ->setDezimalZeichen($sep10)
                        ->setTausenderZeichen($sep1000)
                        ->setSprache($language->kSprache)
                        ->setEinheit($unit)
                        ->save();
                }
            }
            Shop::Container()->getCache()->flushTags([\CACHING_GROUP_CORE]);

            return Shop::Container()->getDB()->getAffectedRows(
                'DELETE teinstellungen, teinstellungenconf
                    FROM teinstellungenconf
                    LEFT JOIN teinstellungen 
                        ON teinstellungen.cName = teinstellungenconf.cWertName
                    WHERE teinstellungenconf.kEinstellungenConf IN (1458, 1459, 495, 497, 499, 501)'
            );
        }

        return false;
    }
}
