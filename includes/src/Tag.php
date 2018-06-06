<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Tag
 */
class Tag
{
    /**
     * @var int
     */
    public $kTag;

    /**
     * @var int
     */
    public $kSprache;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cSeo;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * Konstruktor
     *
     * @param int $kTag - Falls angegeben, wird Tag mit angegebenem kTag aus der DB geholt
     */
    public function __construct(int $kTag = 0)
    {
        if ($kTag > 0) {
            $this->loadFromDB($kTag);
        }
    }

    /**
     * Setzt Tag mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kTag Primary Key
     * @return $this|bool
     */
    private function loadFromDB(int $kTag)
    {
        $obj = Shop::Container()->getDB()->select('ttag', 'kTag', $kTag);
        if (!empty($obj)) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }

        return false;
    }

    /**
     * Gibt Tagobjekt anhand der Sprache und des Namens zurück
     *
     * @param string $cName
     * @return mixed - returns Object if found in DB, else false
     */
    public function getByName($cName = '')
    {
        $cName = StringHandler::filterXSS($cName);
        $obj   = Shop::Container()->getDB()->select('ttag', 'kSprache', Shop::getLanguage(), 'cName', $cName);
        if (!empty($obj)) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }

            return !empty($this->kTag) ? $this : false;
        }

        return false;
    }

    /**
     * Fügt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = kopiereMembers($this);
        unset($obj->kTag);

        return Shop::Container()->getDB()->insert('ttag', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = kopiereMembers($this);

        return Shop::Container()->getDB()->update('ttag', 'kTag', $obj->kTag, $obj);
    }
}
