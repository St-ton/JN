<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Preisverlauf
 */
class Preisverlauf
{
    /**
     * @var int
     */
    public $kPreisverlauf;

    /**
     * @var int
     */
    public $kArtikel;

    /**
     * @var float
     */
    public $fPreisPrivat;

    /**
     * @var float
     */
    public $fPreisHaendler;

    /**
     * @var string
     */
    public $dDate;

    /**
     * Konstruktor
     *
     * @param int $kPreisverlauf - Falls angegeben, wird der Preisverlauf mit angegebenem kPreisverlauf aus der DB geholt
     */
    public function __construct($kPreisverlauf = 0)
    {
        if ($kPreisverlauf > 0) {
            $this->loadFromDB($kPreisverlauf);
        }
    }

    /**
     * @param int $kArtikel
     * @param int $kKundengruppe
     * @param int $nMonat
     * @return mixed
     */
    public function gibPreisverlauf(int $kArtikel, int $kKundengruppe, int $nMonat)
    {
        $cacheID = 'gpv_' . $kArtikel . '_' . $kKundengruppe . '_' . $nMonat;
        if (($obj_arr = Shop::Cache()->get($cacheID)) === false) {
            $obj_arr = Shop::Container()->getDB()->query(
                "SELECT tpreisverlauf.fVKNetto, tartikel.fMwst, UNIX_TIMESTAMP(tpreisverlauf.dDate) AS timestamp
                    FROM tpreisverlauf 
                    LEFT JOIN tartikel
                        ON tartikel.kArtikel = tpreisverlauf.kArtikel
                    WHERE tpreisverlauf.kArtikel = " . $kArtikel . "
                        AND tpreisverlauf.kKundengruppe = " . $kKundengruppe . "
                        AND DATE_SUB(now(), INTERVAL " . $nMonat . " MONTH) < tpreisverlauf.dDate
                    ORDER BY tpreisverlauf.dDate DESC",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            $_currency = Session::Currency();
            $dt        = new DateTime();
            foreach ($obj_arr as &$_pv) {
                if (isset($_pv->timestamp)) {
                    $dt->setTimestamp($_pv->timestamp);
                    $_pv->date   = $dt->format('d.m.');
                    $_pv->fPreis = Session::CustomerGroup()->isMerchant()
                        ? round($_pv->fVKNetto * $_currency->getConversionFactor(), 2)
                        : TaxHelper::getGross($_pv->fVKNetto * $_currency->getConversionFactor(), $_pv->fMwst);
                    $_pv->currency = $_currency->getCode();
                }
            }
            unset($_pv);
            Shop::Cache()->set($cacheID, $obj_arr, [CACHING_GROUP_ARTICLE, CACHING_GROUP_ARTICLE . '_' . $kArtikel]);
        }

        return $obj_arr;
    }

    /**
     * Setzt Preisverlauf mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kPreisverlauf
     * @return $this
     */
    public function loadFromDB(int $kPreisverlauf): self
    {
        $obj = Shop::Container()->getDB()->select('tpreisverlauf', 'kPreisverlauf', $kPreisverlauf);
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
     * @return int
     */
    public function insertInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);
        unset($obj->kPreisverlauf);
        $this->kPreisverlauf = Shop::Container()->getDB()->insert('tpreisverlauf', $obj);

        return $this->kPreisverlauf;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj = ObjectHelper::copyMembers($this);

        return Shop::Container()->getDB()->update('tpreisverlauf', 'kPreisverlauf', $obj->kPreisverlauf, $obj);
    }

    /**
     * setzt Daten aus Sync POST request.
     *
     * @return bool
     * @deprecated since 5.0.0
     */
    public function setzePostDaten(): bool
    {
        return false;
    }
}
