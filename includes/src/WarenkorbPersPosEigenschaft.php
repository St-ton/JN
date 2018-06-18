<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPersPosEigenschaft
 */
class WarenkorbPersPosEigenschaft
{
    /**
     * @var int
     */
    public $kWarenkorbPersPosEigenschaft;

    /**
     * @var int
     */
    public $kWarenkorbPersPos;

    /**
     * @var int
     */
    public $kEigenschaft;

    /**
     * @var int
     */
    public $kEigenschaftWert;

    /**
     * @var string
     */
    public $cFreifeldWert;

    /**
     * @var string
     */
    public $cEigenschaftName;

    /**
     * @var string
     */
    public $cEigenschaftWertName;

    /**
     * @param int    $kEigenschaft
     * @param int    $kEigenschaftWert
     * @param string $cFreifeldWert
     * @param string $cEigenschaftName
     * @param string $cEigenschaftWertName
     * @param int    $kWarenkorbPersPos
     */
    public function __construct(
        int $kEigenschaft,
        int $kEigenschaftWert,
        $cFreifeldWert,
        $cEigenschaftName,
        $cEigenschaftWertName,
        int $kWarenkorbPersPos
    ) {
        $this->kWarenkorbPersPos    = $kWarenkorbPersPos;
        $this->kEigenschaft         = $kEigenschaft;
        $this->kEigenschaftWert     = $kEigenschaftWert;
        $this->cFreifeldWert        = $cFreifeldWert;
        $this->cEigenschaftName     = $cEigenschaftName;
        $this->cEigenschaftWertName = $cEigenschaftWertName;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kWarenkorbPersPosEigenschaft);
        $this->kWarenkorbPersPosEigenschaft = Shop::Container()->getDB()->insert('twarenkorbpersposeigenschaft', $obj);

        return $this;
    }
}
