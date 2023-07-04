<?php

namespace JTL\Checkout;

use JTL\Catalog\Product\EigenschaftWert;
use JTL\DB\DbInterface;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class Eigenschaft
 * @package JTL\Checkout
 */
class Eigenschaft
{
    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var string
     */
    public $cName;

    /**
     * string - 'Y'/'N'
     */
    public $cWaehlbar;

    /**
     * Eigenschaft Wert
     *
     * @var EigenschaftWert
     */
    public $EigenschaftsWert;

    /**
     * @var string
     */
    public $cTyp;

    /**
     * @var int
     */
    public $nSort;

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
        $obj = $this->db->select('teigenschaft', 'kEigenschaft', $id) ?? new \stdClass();
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
        $this->kEigenschaft = (int)$this->kEigenschaft;
        $this->kArtikel     = (int)$this->kArtikel;
        $this->nSort        = (int)$this->nSort;
        \executeHook(\HOOK_EIGENSCHAFT_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->EigenschaftsWert);

        return $this->db->insert('teigenschaft', $obj);
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return $this->db->update('teigenschaft', 'kEigenschaft', $obj->kEigenschaft, $obj);
    }
}
