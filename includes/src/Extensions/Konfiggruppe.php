<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions;

use JsonSerializable;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Shop;
use stdClass;

/**
 * Class Konfiggruppe
 * @package JTL\Extensions
 */
class Konfiggruppe implements JsonSerializable
{
    /**
     * @var int
     */
    protected $kKonfiggruppe;

    /**
     * @var string
     */
    protected $cBildPfad;

    /**
     * @var int
     */
    protected $nMin;

    /**
     * @var int
     */
    protected $nMax;

    /**
     * @var int
     */
    protected $nTyp;

    /**
     * @var int
     */
    protected $nSort;

    /**
     * @var string
     */
    public $cKommentar;

    /**
     * @var object
     */
    public $oSprache;

    /**
     * @var Konfigitem[]
     */
    public $oItem_arr = [];

    /**
     * @var bool|null
     */
    public $bAktiv;

    /**
     * Constructor
     *
     * @param int $kKonfiggruppe
     * @param int $kSprache
     */
    public function __construct(int $kKonfiggruppe = 0, int $kSprache = 0)
    {
        $this->kKonfiggruppe = $kKonfiggruppe;
        if ($this->kKonfiggruppe > 0) {
            $this->loadFromDB($this->kKonfiggruppe, $kSprache);
        }
    }

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->oSprache === null) {
            $this->oSprache = new Konfiggruppesprache($this->kKonfiggruppe);
        }
        $override = [
            'kKonfiggruppe' => (int)$this->kKonfiggruppe,
            'cBildPfad'     => $this->getBildPfad(),
            'nMin'          => (float)$this->nMin,
            'nMax'          => (float)$this->nMax,
            'nTyp'          => (int)$this->nTyp,
            'fInitial'      => (float)$this->getInitQuantity(),
            'bAnzahl'       => $this->getAnzeigeTyp() === \KONFIG_ANZEIGE_TYP_RADIO
                || $this->getAnzeigeTyp() === \KONFIG_ANZEIGE_TYP_DROPDOWN,
            'cName'         => $this->oSprache->getName(),
            'cBeschreibung' => $this->oSprache->getBeschreibung(),
            'oItem_arr'     => $this->oItem_arr
        ];
        $result   = \array_merge(\get_object_vars($this), $override);

        return Text::utf8_convert_recursive($result);
    }

    /**
     * Loads database member into class member
     *
     * @param int $kKonfiggruppe
     * @param int $kSprache
     * @return $this
     */
    private function loadFromDB(int $kKonfiggruppe = 0, int $kSprache = 0): self
    {
        $oObj = Shop::Container()->getDB()->select('tkonfiggruppe', 'kKonfiggruppe', $kKonfiggruppe);
        if (isset($oObj->kKonfiggruppe) && $oObj->kKonfiggruppe > 0) {
            foreach (\array_keys(\get_object_vars($oObj)) as $member) {
                $this->$member = $oObj->$member;
            }
            if (!$kSprache) {
                $kSprache = Shop::getLanguageID();
            }
            $this->kKonfiggruppe = (int)$this->kKonfiggruppe;
            $this->nMin          = (int)$this->nMin;
            $this->nMax          = (int)$this->nMax;
            $this->nTyp          = (int)$this->nTyp;
            $this->nSort         = (int)$this->nSort;
            $this->oSprache      = new Konfiggruppesprache($this->kKonfiggruppe, $kSprache);
            $this->oItem_arr     = Konfigitem::fetchAll($this->kKonfiggruppe);
        }

        return $this;
    }

    /**
     * @param bool $bPrim
     * @return bool|int
     */
    public function save(bool $bPrim = true)
    {
        $ins             = new stdClass();
        $ins->cBildPfad  = $this->cBildPfad;
        $ins->nMin       = $this->nMin;
        $ins->nMax       = $this->nMax;
        $ins->nTyp       = $this->nTyp;
        $ins->nSort      = $this->nSort;
        $ins->cKommentar = $this->cKommentar;

        $kPrim = Shop::Container()->getDB()->insert('tkonfiggruppe', $ins);
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
        $upd             = new stdClass();
        $upd->cBildPfad  = $this->cBildPfad;
        $upd->nMin       = $this->nMin;
        $upd->nMax       = $this->nMax;
        $upd->nTyp       = $this->nTyp;
        $upd->nSort      = $this->nSort;
        $upd->cKommentar = $this->cKommentar;

        return Shop::Container()->getDB()->update(
            'tkonfiggruppe',
            'kKonfiggruppe',
            (int)$this->kKonfiggruppe,
            $upd
        );
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return Shop::Container()->getDB()->delete('tkonfiggruppe', 'kKonfiggruppe', (int)$this->kKonfiggruppe);
    }

    /**
     * @param int $kKonfiggruppe
     * @return $this
     */
    public function setKonfiggruppe(int $kKonfiggruppe): self
    {
        $this->kKonfiggruppe = $kKonfiggruppe;

        return $this;
    }

    /**
     * @param string $cBildPfad
     * @return $this
     */
    public function setBildPfad($cBildPfad): self
    {
        $this->cBildPfad = Shop::Container()->getDB()->escape($cBildPfad);

        return $this;
    }

    /**
     * @param int $nTyp
     * @return $this
     */
    public function setAnzeigeTyp(int $nTyp): self
    {
        $this->nTyp = $nTyp;

        return $this;
    }

    /**
     * @param int $nSort
     * @return $this
     */
    public function setSort(int $nSort): self
    {
        $this->nSort = $nSort;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getKonfiggruppe(): ?int
    {
        return $this->kKonfiggruppe;
    }

    /**
     * @return string|null
     */
    public function getBildPfad(): ?string
    {
        return !empty($this->cBildPfad)
            ? \PFAD_KONFIGURATOR_KLEIN . $this->cBildPfad
            : null;
    }

    /**
     * @return int|null
     */
    public function getMin(): ?int
    {
        return $this->nMin;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->nMax;
    }

    /**
     * @return int
     */
    public function getAuswahlTyp(): int
    {
        return 0;
    }

    /**
     * @return int|null
     */
    public function getAnzeigeTyp(): ?int
    {
        return $this->nTyp;
    }

    /**
     * @return int|null
     */
    public function getSort(): ?int
    {
        return $this->nSort;
    }

    /**
     * @return string|null
     */
    public function getKommentar(): ?string
    {
        return $this->cKommentar;
    }

    /**
     * @return object|null
     */
    public function getSprache()
    {
        return $this->oSprache;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return (int)Shop::Container()->getDB()->query(
            'SELECT COUNT(*) AS nCount 
                FROM tkonfigitem 
                WHERE kKonfiggruppe = ' . (int)$this->kKonfiggruppe,
            ReturnType::SINGLE_OBJECT
        )->nCount;
    }

    /**
     * @return bool
     */
    public function quantityEquals(): bool
    {
        $bEquals = false;
        if (\count($this->oItem_arr) > 0) {
            $oItem = $this->oItem_arr[0];
            if ($oItem->getMin() == $oItem->getMax()) {
                $bEquals = true;
                $nKey    = $oItem->getMin();
                foreach ($this->oItem_arr as &$oItem) {
                    if (!($oItem->getMin() == $oItem->getMax() && $oItem->getMin() == $nKey)) {
                        $bEquals = false;
                    }
                }
            }
        }

        return $bEquals;
    }

    /**
     * @return int|float
     */
    public function getInitQuantity()
    {
        $fQuantity = 1;
        foreach ($this->oItem_arr as &$oItem) {
            if ($oItem->getSelektiert()) {
                $fQuantity = $oItem->getInitial();
            }
        }

        return $fQuantity;
    }

    /**
     * @return bool
     */
    public function allItemsOutOfStock(): bool
    {
        $itemsOutOfStock = 0;
        foreach ($this->oItem_arr as $item) {
            if (!$item->isInStock()) {
                ++$itemsOutOfStock;
            }
        }

        return count($this->oItem_arr) === $itemsOutOfStock;
    }
}
