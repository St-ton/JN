<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\DateHelper;
use Helpers\RequestHelper;

/**
 * Class Kampagne
 */
class Kampagne
{
    /**
     * @var int
     */
    public $kKampagne;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cParameter;

    /**
     * @var string
     */
    public $cWert;

    /**
     * @var int
     */
    public $nDynamisch;

    /**
     * @var int
     */
    public $nAktiv;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * Konstruktor
     *
     * @param int $kKampagne - Falls angegeben, wird die Kampagne mit kKampagne aus der DB geholt
     */
    public function __construct(int $kKampagne = 0)
    {
        if ($kKampagne > 0) {
            $this->loadFromDB($kKampagne);
        }
    }

    /**
     * Setzt Kampagne mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kKampagne - Primary Key
     * @return $this
     */
    public function loadFromDB(int $kKampagne): self
    {
        $oKampagne = Shop::Container()->getDB()->query(
            "SELECT tkampagne.*, DATE_FORMAT(tkampagne.dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE
                FROM tkampagne
                WHERE tkampagne.kKampagne = " . $kKampagne,
            \DB\ReturnType::SINGLE_OBJECT
        );

        if (isset($oKampagne->kKampagne) && $oKampagne->kKampagne > 0) {
            $cMember_arr = array_keys(get_object_vars($oKampagne));
            foreach ($cMember_arr as $cMember) {
                $this->$cMember = $oKampagne->$cMember;
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int
     */
    public function insertInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = $this->cName;
        $obj->cParameter = $this->cParameter;
        $obj->cWert      = $this->cWert;
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;

        $this->kKampagne    = Shop::Container()->getDB()->insert('tkampagne', $obj);
        $cDatum_arr         = DateHelper::getDateParts($this->dErstellt);
        $this->dErstellt_DE = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
            $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];

        return $this->kKampagne;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB(): int
    {
        $obj             = new stdClass();
        $obj->cName      = $this->cName;
        $obj->cParameter = $this->cParameter;
        $obj->cWert      = $this->cWert;
        $obj->nDynamisch = $this->nDynamisch;
        $obj->nAktiv     = $this->nAktiv;
        $obj->dErstellt  = $this->dErstellt;
        $obj->kKampagne  = $this->kKampagne;

        $res                = Shop::Container()->getDB()->update('tkampagne', 'kKampagne', $obj->kKampagne, $obj);
        $cDatum_arr         = DateHelper::getDateParts($this->dErstellt);
        $this->dErstellt_DE = $cDatum_arr['cTag'] . '.' . $cDatum_arr['cMonat'] . '.' . $cDatum_arr['cJahr'] . ' ' .
            $cDatum_arr['cStunde'] . ':' . $cDatum_arr['cMinute'] . ':' . $cDatum_arr['cSekunde'];

        return $res;
    }

    /**
     * @return bool
     */
    public function deleteInDB(): bool
    {
        if ($this->kKampagne > 0) {
            Shop::Container()->getDB()->query(
                'DELETE tkampagne, tkampagnevorgang
                    FROM tkampagne
                    LEFT JOIN tkampagnevorgang 
                        ON tkampagnevorgang.kKampagne = tkampagne.kKampagne
                    WHERE tkampagne.kKampagne = ' . (int)$this->kKampagne,
                \DB\ReturnType::AFFECTED_ROWS
            );

            return true;
        }

        return false;
    }

    /**
     * @return array|mixed
     */
    public static function getAvailable(): array
    {
        $cacheID = 'campaigns';
        if (($oKampagne_arr = Shop::Container()->getCache()->get($cacheID)) === false) {
            $oKampagne_arr = Shop::Container()->getDB()->selectAll(
                'tkampagne',
                'nAktiv',
                1,
                '*, DATE_FORMAT(dErstellt, \'%d.%m.%Y %H:%i:%s\') AS dErstellt_DE'
            );
            $setRes        = Shop::Container()->getCache()->set($cacheID, $oKampagne_arr, [CACHING_GROUP_CORE]);
            if ($setRes === false) {
                // could not save to cache - use session instead
                $campaigns = [];
                if (is_array($oKampagne_arr) && count($oKampagne_arr) > 0) {
                    // save to session
                    foreach ($oKampagne_arr as $oKampagne) {
                        $campaigns[] = $oKampagne;
                    }
                }
                $_SESSION['Kampagnen'] = $campaigns;

                return $_SESSION['Kampagnen'];
            }
        }

        return $oKampagne_arr;
    }

    /**
     * @former pruefeKampagnenParameter()
     */
    public static function checkCampaignParameters(): void
    {
        $campaigns = self::getAvailable();
        if (empty($_SESSION['oBesucher']->kBesucher) || count($campaigns) === 0) {
            return;
        }
        $bKampagnenHit = false;
        foreach ($campaigns as $oKampagne) {
            // Wurde für die aktuelle Kampagne der Parameter via GET oder POST uebergeben?
            if (strlen(RequestHelper::verifyGPDataString($oKampagne->cParameter)) > 0
                && isset($oKampagne->nDynamisch)
                && ((int)$oKampagne->nDynamisch === 1
                    || ((int)$oKampagne->nDynamisch === 0
                        && isset($oKampagne->cWert)
                        && strtolower($oKampagne->cWert) ===
                        strtolower(RequestHelper::verifyGPDataString($oKampagne->cParameter)))
                )
            ) {
                $referrer = Visitor::getReferer();
                //wurde der HIT für diesen Besucher schon gezaehlt?
                $oVorgang = Shop::Container()->getDB()->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey', 'cCustomData'],
                    [
                        KAMPAGNE_DEF_HIT,
                        (int)$oKampagne->kKampagne,
                        (int)$_SESSION['oBesucher']->kBesucher,
                        StringHandler::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer
                    ]
                );

                if (!isset($oVorgang->kKampagneVorgang)) {
                    $event               = new stdClass();
                    $event->kKampagne    = $oKampagne->kKampagne;
                    $event->kKampagneDef = KAMPAGNE_DEF_HIT;
                    $event->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = RequestHelper::verifyGPDataString($oKampagne->cParameter);
                    $event->cCustomData  = StringHandler::filterXSS($_SERVER['REQUEST_URI']) . ';' . $referrer;
                    if ((int)$oKampagne->nDynamisch === 0) {
                        $event->cParamWert = $oKampagne->cWert;
                    }
                    $event->dErstellt = 'NOW()';

                    Shop::Container()->getDB()->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher']        = $oKampagne;
                    $_SESSION['Kampagnenbesucher']->cWert = $event->cParamWert;

                    break;
                }
            }

            if (!$bKampagnenHit
                && isset($_SERVER['HTTP_REFERER'])
                && strpos($_SERVER['HTTP_REFERER'], '.google.') !== false
            ) {
                // Besucher kommt von Google und hat vorher keine Kampagne getroffen
                $oVorgang = Shop::Container()->getDB()->select(
                    'tkampagnevorgang',
                    ['kKampagneDef', 'kKampagne', 'kKey'],
                    [KAMPAGNE_DEF_HIT, KAMPAGNE_INTERN_GOOGLE, (int)$_SESSION['oBesucher']->kBesucher]
                );

                if (!isset($oVorgang->kKampagneVorgang)) {
                    $oKampagne           = new Kampagne(KAMPAGNE_INTERN_GOOGLE);
                    $event               = new stdClass();
                    $event->kKampagne    = KAMPAGNE_INTERN_GOOGLE;
                    $event->kKampagneDef = KAMPAGNE_DEF_HIT;
                    $event->kKey         = $_SESSION['oBesucher']->kBesucher;
                    $event->fWert        = 1.0;
                    $event->cParamWert   = $oKampagne->cWert;
                    $event->dErstellt    = 'NOW()';

                    if ((int)$oKampagne->nDynamisch === 1) {
                        $event->cParamWert = RequestHelper::verifyGPDataString($oKampagne->cParameter);
                    }

                    Shop::Container()->getDB()->insert('tkampagnevorgang', $event);
                    $_SESSION['Kampagnenbesucher']        = $oKampagne;
                    $_SESSION['Kampagnenbesucher']->cWert = $event->cParamWert;
                }
            }
        }
    }

    /**
     * @param int    $id
     * @param int    $kKey
     * @param float  $fWert
     * @param string $customData
     * @return int
     * @former setzeKampagnenVorgang()
     */
    public static function setCampaignAction(int $id, int $kKey, $fWert, $customData = null): int
    {
        if ($id > 0 && $kKey > 0 && $fWert > 0 && isset($_SESSION['Kampagnenbesucher'])) {
            $oKampagnenVorgang               = new stdClass();
            $oKampagnenVorgang->kKampagne    = $_SESSION['Kampagnenbesucher']->kKampagne;
            $oKampagnenVorgang->kKampagneDef = $id;
            $oKampagnenVorgang->kKey         = $kKey;
            $oKampagnenVorgang->fWert        = $fWert;
            $oKampagnenVorgang->cParamWert   = $_SESSION['Kampagnenbesucher']->cWert;
            $oKampagnenVorgang->dErstellt    = 'NOW()';

            if ($customData !== null) {
                $oKampagnenVorgang->cCustomData = strlen($customData) > 255
                    ? substr($customData, 0, 255)
                    : $customData;
            }

            return Shop::Container()->getDB()->insert('tkampagnevorgang', $oKampagnenVorgang);
        }

        return 0;
    }
}
