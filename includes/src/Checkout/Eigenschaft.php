<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Checkout;

use JTL\Catalog\Product\EigenschaftWert;
use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class Eigenschaft
 * @package JTL
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
     * Eigenschaft constructor.
     * @param int $kEigenschaft
     */
    public function __construct(int $kEigenschaft = 0)
    {
        if ($kEigenschaft > 0) {
            $this->loadFromDB($kEigenschaft);
        }
    }

    /**
     * @param int $kEigenschaft
     * @return $this
     */
    public function loadFromDB(int $kEigenschaft): self
    {
        $obj = Shop::Container()->getDB()->select('teigenschaft', 'kEigenschaft', $kEigenschaft);
        foreach (\get_object_vars($obj) as $k => $v) {
            $this->$k = $v;
        }
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

        return Shop::Container()->getDB()->insert('teigenschaft', $obj);
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update('teigenschaft', 'kEigenschaft', $obj->kEigenschaft, $obj);
    }

    /**
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
