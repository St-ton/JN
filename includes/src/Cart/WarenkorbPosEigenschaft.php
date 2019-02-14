<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cart;

use JTL\Helpers\GeneralObject;
use JTL\Shop;

/**
 * Class WarenkorbPosEigenschaft
 * @package JTL\Cart
 */
class WarenkorbPosEigenschaft
{
    /**
     * @var int
     */
    public $kWarenkorbPosEigenschaft;

    /**
     * @var int
     */
    public $kWarenkorbPos;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kEigenschaftWert;
    /**
     * @var float
     */
    public $fAufpreis;

    /**
     * @var float
     */
    public $fGewichtsdifferenz;

    /**
     * @var string
     */
    public $cEigenschaftName;

    /**
     * @var string
     */
    public $cEigenschaftWertName;

    /**
     * @var string
     */
    public $cAufpreisLocalized;

    /**
     * @var string
     */
    public $cTyp;

    /**
     * WarenkorbPosEigenschaft constructor.
     * @param int $kWarenkorbPosEigenschaft
     */
    public function __construct(int $kWarenkorbPosEigenschaft = 0)
    {
        if ($kWarenkorbPosEigenschaft > 0) {
            $this->loadFromDB($kWarenkorbPosEigenschaft);
        }
    }

    /**
     * gibt Namen der Eigenschaft zurück
     *
     * @return string - EigenschaftName
     */
    public function gibEigenschaftName(): string
    {
        $obj = Shop::Container()->getDB()->select('teigenschaft', 'kEigenschaft', $this->kEigenschaft);

        return $obj->cName ?? '';
    }

    /**
     * gibt Namen des EigenschaftWerts zurück
     *
     * @return string - EigenschaftWertName
     */
    public function gibEigenschaftWertName(): string
    {
        $obj = Shop::Container()->getDB()->select('teigenschaftwert', 'kEigenschaftWert', $this->kEigenschaftWert);

        return $obj->cName ?? '';
    }

    /**
     * @param int $kWarenkorbPosEigenschaft
     * @return $this
     */
    public function loadFromDB(int $kWarenkorbPosEigenschaft): self
    {
        $obj = Shop::Container()->getDB()->select(
            'twarenkorbposeigenschaft',
            'kWarenkorbPosEigenschaft',
            $kWarenkorbPosEigenschaft
        );
        if ($obj !== null) {
            $members = \array_keys(\get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function insertInDB(): self
    {
        $obj = GeneralObject::copyMembers($this);
        unset($obj->kWarenkorbPosEigenschaft, $obj->cAufpreisLocalized, $obj->fGewichtsdifferenz, $obj->cTyp);
        //sql strict mode
        if ($obj->fAufpreis === null || $obj->fAufpreis === '') {
            $obj->fAufpreis = 0;
        }
        $this->kWarenkorbPosEigenschaft = Shop::Container()->getDB()->insert('twarenkorbposeigenschaft', $obj);

        return $this;
    }

    /**
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = GeneralObject::copyMembers($this);

        return Shop::Container()->getDB()->update(
            'twarenkorbposeigenschaft',
            'kWarenkorbPosEigenschaft',
            $obj->kWarenkorbPosEigenschaft,
            $obj
        );
    }
}
