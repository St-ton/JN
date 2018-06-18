<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WunschlistePosEigenschaft
 */
class WunschlistePosEigenschaft
{
    /**
     * @var int
     */
    public $kWunschlistePosEigenschaft;

    /**
     * @var int
     */
    public $kWunschlistePos;

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
     * @param int    $kWunschlistePos
     */
    public function __construct(
        int $kEigenschaft,
        int $kEigenschaftWert,
        $cFreifeldWert,
        $cEigenschaftName,
        $cEigenschaftWertName,
        int $kWunschlistePos
    ) {
        $this->kEigenschaft         = $kEigenschaft;
        $this->kEigenschaftWert     = $kEigenschaftWert;
        $this->kWunschlistePos      = $kWunschlistePos;
        $this->cFreifeldWert        = $cFreifeldWert;
        $this->cEigenschaftName     = $cEigenschaftName;
        $this->cEigenschaftWertName = $cEigenschaftWertName;
    }

    /**
     * @return $this
     */
    public function schreibeDB()
    {
        $this->kWunschlistePosEigenschaft = Shop::Container()->getDB()->insert(
            'twunschlisteposeigenschaft',
            ObjectHelper::copyMembers($this)
        );

        return $this;
    }
}
