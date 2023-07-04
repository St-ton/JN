<?php

namespace JTL\Catalog\Product;

use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class EigenschaftWert
 * @package JTL\Catalog\Product
 */
class EigenschaftWert
{
    /**
     * @var int
     */
    public int $kEigenschaftWert = 0;

    /**
     * @var int
     */
    public int $kEigenschaft = 0;

    /**
     * @var float|string|null
     */
    public $fAufpreisNetto;

    /**
     * @var float|string|null
     */
    public $fGewichtDiff;

    /**
     * @var float|string|null
     */
    public $fLagerbestand;

    /**
     * @var float|string|null
     */
    public $fPackeinheit;

    /**
     * @var string
     */
    public string $cName = '';

    /**
     * @var float|string|null
     */
    public $fAufpreis;

    /**
     * @var int
     */
    public int $nSort = 0;

    /**
     * @var string
     */
    public string $cArtNr = '';

    /**
     * @param int              $id
     * @param DbInterface|null $db
     */
    public function __construct(int $id = 0, private ?DbInterface $db = null)
    {
        $this->db = $this->db ?? Shop::Container()->getDB();
        if ($id > 0) {
            $this->loadFromDB($id);
        }
    }

    /**
     * @param int $id
     * @return $this
     */
    public function loadFromDB(int $id): self
    {
        if ($id <= 0) {
            return $this;
        }
        $data = $this->db->select('teigenschaftwert', 'kEigenschaftWert', $id);
        if ($data !== null && $data->kEigenschaftWert > 0) {
            $this->kEigenschaft     = (int)$data->kEigenschaft;
            $this->kEigenschaftWert = (int)$data->kEigenschaftWert;
            $this->nSort            = (int)$data->nSort;
            $this->cName            = $data->cName;
            $this->fAufpreisNetto   = $data->fAufpreisNetto;
            $this->fGewichtDiff     = $data->fGewichtDiff;
            $this->cArtNr           = $data->cArtNr;
            $this->fLagerbestand    = $data->fLagerbestand;
            $this->fPackeinheit     = $data->fPackeinheit;
            if ($this->fPackeinheit == 0) {
                $this->fPackeinheit = 1;
            }
        }
        \executeHook(\HOOK_EIGENSCHAFTWERT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->fAufpreis);

        return $this->db->insert('teigenschaftwert', $obj);
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->fAufpreis);

        return $this->db->update('teigenschaftwert', 'kEigenschaftWert', $obj->kEigenschaftWert, $obj);
    }
}
