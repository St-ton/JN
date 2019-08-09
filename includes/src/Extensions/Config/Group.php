<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Extensions\Config;

use JsonSerializable;
use JTL\DB\ReturnType;
use JTL\Helpers\Text;
use JTL\Nice;
use JTL\Shop;

/**
 * Class Group
 * @package JTL\Extensions\Config
 */
class Group implements JsonSerializable
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
     * @var Item[]
     */
    public $oItem_arr = [];

    /**
     * @var bool|null
     */
    public $bAktiv;

    /**
     * Group constructor.
     * @param int $id
     * @param int $languageID
     */
    public function __construct(int $id = 0, int $languageID = 0)
    {
        $this->kKonfiggruppe = $id;
        if ($this->kKonfiggruppe > 0) {
            $this->loadFromDB($this->kKonfiggruppe, $languageID);
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
            $this->oSprache = new GroupLocalization($this->kKonfiggruppe);
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
     * @param int $id
     * @param int $languageID
     * @return $this
     */
    private function loadFromDB(int $id = 0, int $languageID = 0): self
    {
        $data = Shop::Container()->getDB()->select('tkonfiggruppe', 'kKonfiggruppe', $id);
        if (isset($data->kKonfiggruppe) && $data->kKonfiggruppe > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
            if (!$languageID) {
                $languageID = Shop::getLanguageID();
            }
            $this->kKonfiggruppe = (int)$this->kKonfiggruppe;
            $this->nMin          = (int)$this->nMin;
            $this->nMax          = (int)$this->nMax;
            $this->nTyp          = (int)$this->nTyp;
            $this->nSort         = (int)$this->nSort;
            $this->oSprache      = new GroupLocalization($this->kKonfiggruppe, $languageID);
            $this->oItem_arr     = Item::fetchAll($this->kKonfiggruppe);
        }

        return $this;
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function save(): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return false;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function update(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @return int
     * @deprecated since 5.0.0
     */
    public function delete(): int
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return 0;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setKonfiggruppe(int $id): self
    {
        $this->kKonfiggruppe = $id;

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
        $equal = false;
        if (\count($this->oItem_arr) > 0) {
            $item = $this->oItem_arr[0];
            if ($item->getMin() === $item->getMax()) {
                $equal = true;
                $nKey  = $item->getMin();
                foreach ($this->oItem_arr as &$item) {
                    if (!($item->getMin() === $item->getMax() && $item->getMin() === $nKey)) {
                        $equal = false;
                    }
                }
            }
        }

        return $equal;
    }

    /**
     * @return int|float
     */
    public function getInitQuantity()
    {
        $qty = 1;
        foreach ($this->oItem_arr as &$item) {
            if ($item->getSelektiert()) {
                $qty = $item->getInitial();
            }
        }

        return $qty;
    }

    /**
     * @return bool
     */
    public function minItemsInStock(): bool
    {
        $inStockCount = 0;
        foreach ($this->oItem_arr as $item) {
            if ($item->isInStock() && ++$inStockCount >= $this->nMin) {
                return true;
            }
        }

        return false;
    }
}
