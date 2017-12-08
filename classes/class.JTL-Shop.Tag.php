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
    public function __construct($kTag = 0)
    {
        $kTag = (int)$kTag;
        if ($kTag > 0) {
            $this->loadFromDB($kTag);
        }
    }

    /**
     * Setzt Tag mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kTag Primary Key
     * @return $this
     */
    public function loadFromDB($kTag)
    {
        $obj = Shop::DB()->select('ttag', 'kTag', (int)$kTag);
        if (!empty($obj)) {
            foreach (get_object_vars($obj) as $k => $v) {
                $this->$k = $v;
            }

            return $this;
        }
        return false;
    }

    /**
     * Setzt Tag mit Daten aus der DB mit spezifiziertem cName
     *
     * @access public
     * @param string $cName
     * @return mixed - returns Object if found in DB, else false
     */
    public function loadViaName($cName = '')
    {
        $cName = StringHandler::filterXSS($cName);
        $obj = Shop::DB()->select('ttag', 'kSprache', Shop::getLanguage(), 'cName', $cName);
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
     * @access public
     * @return mixed
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kTag);

        return Shop::DB()->insert('ttag', $obj);
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);

        return Shop::DB()->update('ttag', 'kTag', $obj->kTag, $obj);
    }
}
