<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Lieferadresse
 */
class Lieferadresse extends Adresse
{
    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var string
     */
    public $cAnredeLocalized;

    /**
     * @var string
     */
    public $angezeigtesLand;

    /**
     * Konstruktor
     *
     * @param int $kLieferadresse
     */
    public function __construct(int $kLieferadresse = 0)
    {
        if ($kLieferadresse > 0) {
            $this->loadFromDB($kLieferadresse);
        }
    }

    /**
     * @param int $kLieferadresse
     * @return Lieferadresse|int
     */
    public function loadFromDB(int $kLieferadresse)
    {
        $obj = Shop::Container()->getDB()->select('tlieferadresse', 'kLieferadresse', $kLieferadresse);

        if ($obj === null) {
            return 0;
        }

        $this->fromObject($obj);
        // Anrede mappen
        $this->cAnredeLocalized = Kunde::mapSalutation($this->cAnrede, 0, $this->kKunde);
        $this->angezeigtesLand  = Sprache::getCountryCodeByCountryName($this->cLand);
        if ($this->kLieferadresse > 0) {
            $this->decrypt();
        }

        executeHook(HOOK_LIEFERADRESSE_CLASS_LOADFROMDB);

        return $this;
    }

    /**
     * @return int
     */
    public function insertInDB(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->kLieferadresse, $obj->angezeigtesLand, $obj->cAnredeLocalized);

        $this->kLieferadresse = Shop::Container()->getDB()->insert('tlieferadresse', $obj);
        $this->decrypt();
        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $this->kLieferadresse;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $this->encrypt();
        $obj = $this->toObject();

        $obj->cLand = $this->pruefeLandISO($obj->cLand);

        unset($obj->angezeigtesLand, $obj->cAnredeLocalized);

        $cReturn = Shop::Container()->getDB()->update('tlieferadresse', 'kLieferadresse', $obj->kLieferadresse, $obj);
        $this->decrypt();

        // Anrede mappen
        $this->cAnredeLocalized = $this->mappeAnrede($this->cAnrede);

        return $cReturn;
    }

    /**
     * get shipping address
     *
     * @return array
     */
    public function gibLieferadresseAssoc(): array
    {
        return $this->kLieferadresse > 0
            ? $this->toArray()
            : [];
    }
}
