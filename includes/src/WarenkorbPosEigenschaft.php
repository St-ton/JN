<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class WarenkorbPosEigenschaft
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
     * Konstruktor
     *
     * @param int $kWarenkorbPosEigenschaft - Falls angegeben,
     * wird der WarenkorbPosEigenschaft mit angegebenem kWarenkorbPosEigenschaft aus der DB geholt
     */
    public function __construct($kWarenkorbPosEigenschaft = 0)
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
    public function gibEigenschaftName()
    {
        $obj = Shop::Container()->getDB()->select('teigenschaft', 'kEigenschaft', $this->kEigenschaft);

        return $obj->cName ?? '';
    }

    /**
     * gibt Namen des EigenschaftWerts zurück
     *
     * @return string - EigenschaftWertName
     */
    public function gibEigenschaftWertName()
    {
        $obj = Shop::Container()->getDB()->select('teigenschaftwert', 'kEigenschaftWert', $this->kEigenschaftWert);

        return $obj->cName ?? '';
    }

    /**
     * Setzt WarenkorbPosEigenschaft mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kWarenkorbPosEigenschaft
     * @return $this
     */
    public function loadFromDB($kWarenkorbPosEigenschaft)
    {
        $obj     = Shop::Container()->getDB()->select(
            'twarenkorbposeigenschaft',
            'kWarenkorbPosEigenschaft',
            (int)$kWarenkorbPosEigenschaft
        );
        if ($obj !== null) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return $this
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kWarenkorbPosEigenschaft, $obj->cAufpreisLocalized, $obj->fGewichtsdifferenz, $obj->cTyp);
        //sql strict mode
        if ($obj->fAufpreis === null || $obj->fAufpreis === '') {
            $obj->fAufpreis = 0;
        }
        $this->kWarenkorbPosEigenschaft = Shop::Container()->getDB()->insert('twarenkorbposeigenschaft', $obj);

        return $this;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update(
            'twarenkorbposeigenschaft',
            'kWarenkorbPosEigenschaft',
            $obj->kWarenkorbPosEigenschaft,
            $obj
        );
    }
}
