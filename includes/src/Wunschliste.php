<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Wunschliste
 */
class Wunschliste
{
    /**
     * @var int
     */
    public $kWunschliste;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $nStandard;

    /**
     * @var int
     */
    public $nOeffentlich;

    /**
     * @var string
     */
    public $cName;

    /**
     * @var string
     */
    public $cURLID;

    /**
     * @var string
     */
    public $dErstellt;

    /**
     * @var string
     */
    public $dErstellt_DE;

    /**
     * @var array
     */
    public $CWunschlistePos_arr = [];

    /**
     * @var Kunde
     */
    public $oKunde;

    /**
     * @param int $kWunschliste
     */
    public function __construct($kWunschliste = 0)
    {
        $kWunschliste = (int)$kWunschliste;
        if ($kWunschliste > 0) {
            $this->kWunschliste = $kWunschliste;
            $this->ladeWunschliste();
        } else {
            $this->kKunde       = isset($_SESSION['Kunde']->kKunde) ? (int)$_SESSION['Kunde']->kKunde : 0;
            $this->nStandard    = 1;
            $this->nOeffentlich = 0;
            $this->cName        = Shop::Lang()->get('wishlist');
            $this->dErstellt    = 'now()';
            $this->cURLID       = '';
        }
    }

    /**
     * fügt eine Position zur Wunschliste hinzu
     *
     * @param int    $kArtikel
     * @param string $cArtikelName
     * @param array  $oEigenschaftwerte_arr
     * @param float  $fAnzahl
     * @return int
     */
    public function fuegeEin($kArtikel, $cArtikelName, $oEigenschaftwerte_arr, $fAnzahl)
    {
        $bBereitsEnthalten = false;
        $nPosition         = 0;
        $kArtikel          = (int)$kArtikel;
        foreach ($this->CWunschlistePos_arr as $i => $CWunschlistePos) {
            $CWunschlistePos->kArtikel = (int)$CWunschlistePos->kArtikel;
            if ($bBereitsEnthalten) {
                break;
            }

            if ($CWunschlistePos->kArtikel === $kArtikel) {
                $nPosition         = $i;
                $bBereitsEnthalten = true;
                if (count($CWunschlistePos->CWunschlistePosEigenschaft_arr) > 0) {
                    foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                        if (!$CWunschlistePos->istEigenschaftEnthalten(
                            $oEigenschaftwerte->kEigenschaft,
                            $oEigenschaftwerte->kEigenschaftWert)
                        ) {
                            $bBereitsEnthalten = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($bBereitsEnthalten) {
            $this->CWunschlistePos_arr[$nPosition]->fAnzahl += $fAnzahl;
            $this->CWunschlistePos_arr[$nPosition]->updateDB();
            $kWunschlistePos = $this->CWunschlistePos_arr[$nPosition]->kWunschlistePos;
        } else {
            $CWunschlistePos                = new WunschlistePos($kArtikel, $cArtikelName, $fAnzahl, $this->kWunschliste);
            $CWunschlistePos->dHinzugefuegt = date('Y-m-d H:i:s');
            $CWunschlistePos->schreibeDB();
            $kWunschlistePos = $CWunschlistePos->kWunschlistePos;
            $CWunschlistePos->erstellePosEigenschaften($oEigenschaftwerte_arr);
            $CArtikel = new Artikel();
            $CArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            $CWunschlistePos->Artikel    = $CArtikel;
            $this->CWunschlistePos_arr[] = $CWunschlistePos;
        }

        executeHook(HOOK_WUNSCHLISTE_CLASS_FUEGEEIN);

        return $kWunschlistePos;
    }

    /**
     * @param int $kWunschlistePos
     * @return $this
     */
    public function entfernePos($kWunschlistePos)
    {
        $kWunschlistePos = (int)$kWunschlistePos;
        $oKunde          = Shop::DB()->queryPrepared(
            'SELECT twunschliste.kKunde
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                WHERE twunschlistepos.kWunschlistePos = :wlID',
            ['wlID' => $kWunschlistePos],
            NiceDB::RET_SINGLE_OBJECT
        );

        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WunschlistenPos ist
        if (!empty($oKunde->kKunde) && $oKunde->kKunde == $_SESSION['Kunde']->kKunde) {
            // Alle Eigenschaften löschen
            Shop::DB()->delete('twunschlisteposeigenschaft', 'kWunschlistePos', $kWunschlistePos);

            // Die Posiotion mit ID $kWunschlistePos löschen
            Shop::DB()->delete('twunschlistepos', 'kWunschlistePos', $kWunschlistePos);

            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $CWunschlistePos) {
                if ($CWunschlistePos->kWunschlistePos == $kWunschlistePos) {
                    unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                }
            }

            // Positionen Array in der Wunschliste neu nummerieren
            $_SESSION['Wunschliste']->CWunschlistePos_arr = array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function entferneAllePos()
    {
        return Shop::DB()->query(
            'DELETE twunschlistepos, twunschlisteposeigenschaft 
                FROM twunschlistepos
                LEFT JOIN twunschlisteposeigenschaft 
                    ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                WHERE twunschlistepos.kWunschliste = :wlID',
            ['wlID' => (int)$this->kWunschliste],
            NiceDB::RET_AFFECTED_ROWS
        );
    }

    /**
     * Falls die Einstellung global_wunschliste_artikel_loeschen_nach_kauf auf Y (Ja) steht und
     * Artikel vom aktuellen Wunschzettel gekauft wurden, sollen diese vom Wunschzettel geloescht werden
     *
     * @param int   $kWunschliste
     * @param array $oWarenkorbpositionen_arr
     * @return bool|int
     */
    public static function pruefeArtikelnachBestellungLoeschen($kWunschliste, $oWarenkorbpositionen_arr)
    {
        $kWunschliste = (int)$kWunschliste;
        $conf         = Shop::getSettings([CONF_GLOBAL]);
        if ($kWunschliste < 1 || $conf['global']['global_wunschliste_artikel_loeschen_nach_kauf'] !== 'Y') {
            return false;
        }
        $nCount        = 0;
        $oWunschzettel = new self($kWunschliste);
        if (!($oWunschzettel->kWunschliste > 0
            && is_array($oWarenkorbpositionen_arr)
            && count($oWunschzettel->CWunschlistePos_arr) > 0
            && count($oWarenkorbpositionen_arr) > 0)
        ) {
            return false;
        }
        foreach ($oWunschzettel->CWunschlistePos_arr as $oWunschlistePos) {
            foreach ($oWarenkorbpositionen_arr as $oArtikel) {
                if ($oWunschlistePos->kArtikel != $oArtikel->kArtikel) {
                    continue;
                }
                //mehrfache Variationen beachten
                if (!empty($oWunschlistePos->CWunschlistePosEigenschaft_arr)
                    && !empty($oArtikel->WarenkorbPosEigenschaftArr)
                ) {
                    $nMatchesFound = 0;
                    $index         = 0;
                    foreach ($oWunschlistePos->CWunschlistePosEigenschaft_arr as $oWPEigenschaft){
                        if ($index === $nMatchesFound) {
                            foreach ($oArtikel->WarenkorbPosEigenschaftArr as $oAEigenschaft){
                                if ($oWPEigenschaft->kEigenschaftWert != 0
                                    && $oWPEigenschaft->kEigenschaftWert === $oAEigenschaft->kEigenschaftWert
                                ){
                                    ++$nMatchesFound;
                                    break;
                                }
                                if ($oWPEigenschaft->kEigenschaftWert === 0
                                    && $oAEigenschaft->kEigenschaftWert === 0
                                    && !empty($oWPEigenschaft->cFreifeldWert)
                                    && !empty($oAEigenschaft->cFreifeldWert)
                                    && $oWPEigenschaft->cFreifeldWert === $oAEigenschaft->cFreifeldWert
                                ) {
                                    ++$nMatchesFound;
                                    break;
                                }
                            }
                        }
                        ++$index;
                    }
                    if ($nMatchesFound === count($oArtikel->WarenkorbPosEigenschaftArr)) {
                        $oWunschzettel->entfernePos($oWunschlistePos->kWunschlistePos);
                    }
                } else {
                    $oWunschzettel->entfernePos($oWunschlistePos->kWunschlistePos);
                }
                ++$nCount;
            }
        }

        return $nCount;
    }

    /**
     * @param string $cSuche
     * @return array
     */
    public function sucheInWunschliste($cSuche)
    {
        if (empty($cSuche)) {
            return [];
        }
        $oWunschlistePosSuche_arr = [];
        $oSuchergebnis_arr        = Shop::DB()->queryPrepared(
            "SELECT twunschlistepos.*, date_format(twunschlistepos.dHinzugefuegt, '%d.%m.%Y %H:%i') AS dHinzugefuegt_de
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschlistepos.kWunschliste = twunschliste.kWunschliste
                    AND (twunschlistepos.cArtikelName LIKE :search
                    OR twunschlistepos.cKommentar LIKE :search)
                WHERE twunschliste.kWunschliste = :wlID",
            [
                'search' => '%' . $cSuche . '%',
                'wlID'   => (int)$this->kWunschliste
            ],
            NiceDB::RET_ARRAY_OF_OBJECTS
        );
        foreach ($oSuchergebnis_arr as $i => $oSuchergebnis) {
            $oWunschlistePosSuche_arr[$i] = new WunschlistePos(
                $oSuchergebnis->kArtikel,
                $oSuchergebnis->cArtikelName,
                $oSuchergebnis->fAnzahl,
                $oSuchergebnis->kWunschliste
            );

            $oWunschlistePosSuche_arr[$i]->kWunschlistePos  = $oSuchergebnis->kWunschlistePos;
            $oWunschlistePosSuche_arr[$i]->cKommentar       = $oSuchergebnis->cKommentar;
            $oWunschlistePosSuche_arr[$i]->dHinzugefuegt    = $oSuchergebnis->dHinzugefuegt;
            $oWunschlistePosSuche_arr[$i]->dHinzugefuegt_de = $oSuchergebnis->dHinzugefuegt_de;

            $WunschlistePosEigenschaft_arr = Shop::DB()->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*, teigenschaftsprache.cName
                    FROM twunschlisteposeigenschaft
                    JOIN teigenschaftsprache 
                        ON teigenschaftsprache.kEigenschaft = twunschlisteposeigenschaft.kEigenschaft
                    WHERE twunschlisteposeigenschaft.kWunschlistePos = :wlID
                    GROUP BY twunschlisteposeigenschaft.kWunschlistePosEigenschaft',
                ['wlID' => (int)$oSuchergebnis->kWunschlistePos],
                NiceDB::RET_ARRAY_OF_OBJECTS
            );
            foreach ($WunschlistePosEigenschaft_arr as $WunschlistePosEigenschaft) {
                if (strlen($WunschlistePosEigenschaft->cFreifeldWert) > 0) {
                    $WunschlistePosEigenschaft->cEigenschaftName     = $WunschlistePosEigenschaft->cName;
                    $WunschlistePosEigenschaft->cEigenschaftWertName = $WunschlistePosEigenschaft->cFreifeldWert;
                }
                $CWunschlistePosEigenschaft = new WunschlistePosEigenschaft(
                    $WunschlistePosEigenschaft->kEigenschaft,
                    $WunschlistePosEigenschaft->kEigenschaftWert,
                    $WunschlistePosEigenschaft->cFreifeldWert,
                    $WunschlistePosEigenschaft->cEigenschaftName,
                    $WunschlistePosEigenschaft->cEigenschaftWertName,
                    $WunschlistePosEigenschaft->kWunschlistePos
                );

                $CWunschlistePosEigenschaft->kWunschlistePosEigenschaft = $WunschlistePosEigenschaft->kWunschlistePosEigenschaft;

                $oWunschlistePosSuche_arr[$i]->CWunschlistePosEigenschaft_arr[] = $CWunschlistePosEigenschaft;
            }

            $oWunschlistePosSuche_arr[$i]->Artikel = new Artikel();
            $oWunschlistePosSuche_arr[$i]->Artikel->fuelleArtikel($oSuchergebnis->kArtikel, Artikel::getDefaultOptions());
            $oWunschlistePosSuche_arr[$i]->cArtikelName = $oWunschlistePosSuche_arr[$i]->Artikel->cName;

            if (Session::CustomerGroup()->isMerchant()) {
                $fPreis = (int)$oWunschlistePosSuche_arr[$i]->fAnzahl *
                    $oWunschlistePosSuche_arr[$i]->Artikel->Preise->fVKNetto;
            } else {
                $fPreis = (int)$oWunschlistePosSuche_arr[$i]->fAnzahl *
                    ($oWunschlistePosSuche_arr[$i]->Artikel->Preise->fVKNetto *
                        (100 + $_SESSION['Steuersatz'][$oWunschlistePosSuche_arr[$i]->Artikel->kSteuerklasse]) /
                        100);
            }

            $oWunschlistePosSuche_arr[$i]->cPreis = gibPreisStringLocalized($fPreis, Session::Currency());
        }

        return $oWunschlistePosSuche_arr;
    }

    /**
     * @return $this
     */
    public function schreibeDB()
    {
        $oTemp               = new stdClass();
        $oTemp->kKunde       = $this->kKunde;
        $oTemp->cName        = $this->cName;
        $oTemp->nStandard    = $this->nStandard;
        $oTemp->nOeffentlich = $this->nOeffentlich;
        $oTemp->dErstellt    = $this->dErstellt;
        $oTemp->cURLID       = $this->cURLID;

        $this->kWunschliste = Shop::DB()->insert('twunschliste', $oTemp);

        return $this;
    }

    /**
     * @return $this
     */
    public function ladeWunschliste()
    {
        // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
        $oWunschliste = Shop::DB()->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM twunschliste
                WHERE kWunschliste = :wlID",
            ['wlID' => (int)$this->kWunschliste],
            NiceDB::RET_SINGLE_OBJECT
        );
        $this->kWunschliste = (int)$oWunschliste->kWunschliste;
        $this->kKunde       = (int)$oWunschliste->kKunde;
        $this->nStandard    = (int)$oWunschliste->nStandard;
        $this->nOeffentlich = (int)$oWunschliste->nOeffentlich;
        $this->cName        = $oWunschliste->cName;
        $this->cURLID       = $oWunschliste->cURLID;
        $this->dErstellt    = $oWunschliste->dErstellt;
        $this->dErstellt_DE = $oWunschliste->dErstellt_DE;
        // Kunde holen
        if ((int)$this->kKunde > 0) {
            $this->oKunde = new Kunde($this->kKunde);
            unset($this->oKunde->cPasswort, $this->oKunde->fRabatt, $this->oKunde->fGuthaben, $this->oKunde->cUSTID);
        }
        $langID = Shop::getLanguageID();
        // Hole alle Positionen für eine Wunschliste
        $WunschlistePos_arr = Shop::DB()->selectAll
        ('twunschlistepos',
            'kWunschliste',
            (int)$this->kWunschliste,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de'
        );
        $defaultOptions = Artikel::getDefaultOptions();
        // Hole alle Eigenschaften für eine Position
        foreach ($WunschlistePos_arr as $WunschlistePos) {
            $CWunschlistePos = new WunschlistePos(
                $WunschlistePos->kArtikel,
                $WunschlistePos->cArtikelName,
                $WunschlistePos->fAnzahl,
                $WunschlistePos->kWunschliste
            );

            $cArtikelName                      = $CWunschlistePos->cArtikelName;
            $CWunschlistePos->kWunschlistePos  = (int)$WunschlistePos->kWunschlistePos;
            $CWunschlistePos->cKommentar       = $WunschlistePos->cKommentar;
            $CWunschlistePos->dHinzugefuegt    = $WunschlistePos->dHinzugefuegt;
            $CWunschlistePos->dHinzugefuegt_de = $WunschlistePos->dHinzugefuegt_de;

            $WunschlistePosEigenschaft_arr = Shop::DB()->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*, 
                    IF(LENGTH(teigenschaftsprache.cName) > 0, teigenschaftsprache.cName, twunschlisteposeigenschaft.cEigenschaftName) AS cName,
                    IF(LENGTH(teigenschaftwertsprache.cName) > 0, teigenschaftwertsprache.cName, twunschlisteposeigenschaft.cEigenschaftWertName) AS cWert
                    FROM twunschlisteposeigenschaft
                    LEFT JOIN teigenschaftsprache 
                        ON teigenschaftsprache.kEigenschaft = twunschlisteposeigenschaft.kEigenschaft
                        AND teigenschaftsprache.kSprache = :langID
                    LEFT JOIN teigenschaftwertsprache 
                            ON teigenschaftwertsprache.kEigenschaftWert = twunschlisteposeigenschaft.kEigenschaftWert
                        AND teigenschaftwertsprache.kSprache = :langID
                    WHERE twunschlisteposeigenschaft.kWunschlistePos = :wlID
                    GROUP BY twunschlisteposeigenschaft.kWunschlistePosEigenschaft',
                [
                    'langID' => $langID,
                    'wlID'   => (int)$WunschlistePos->kWunschlistePos
                ],
                NiceDB::RET_ARRAY_OF_OBJECTS
            );
            foreach ($WunschlistePosEigenschaft_arr as $WunschlistePosEigenschaft) {
                if (strlen($WunschlistePosEigenschaft->cFreifeldWert) > 0) {
                    if (empty($WunschlistePosEigenschaft->cName)) {
                        $_cName = Shop::DB()->queryPrepared(
                            "SELECT IF(LENGTH(teigenschaftsprache.cName) > 0, teigenschaftsprache.cName, teigenschaft.cName) AS cName
                                FROM teigenschaft
                                LEFT JOIN teigenschaftsprache 
                                    ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                    AND teigenschaftsprache.kSprache = :langID
                                WHERE teigenschaft.kEigenschaft = :attrID",
                            [
                                'langID' => $langID,
                                'attrID' => (int)$WunschlistePosEigenschaft->kEigenschaft
                            ],
                            NiceDB::RET_SINGLE_OBJECT
                        );
                        $WunschlistePosEigenschaft->cName = $_cName->cName;
                    }
                    $WunschlistePosEigenschaft->cWert = $WunschlistePosEigenschaft->cFreifeldWert;
                }

                $CWunschlistePosEigenschaft = new WunschlistePosEigenschaft(
                    $WunschlistePosEigenschaft->kEigenschaft,
                    $WunschlistePosEigenschaft->kEigenschaftWert,
                    $WunschlistePosEigenschaft->cFreifeldWert,
                    $WunschlistePosEigenschaft->cName,
                    $WunschlistePosEigenschaft->cWert,
                    $WunschlistePosEigenschaft->kWunschlistePos);

                $CWunschlistePosEigenschaft->kWunschlistePosEigenschaft = (int)$WunschlistePosEigenschaft->kWunschlistePosEigenschaft;
                $CWunschlistePos->CWunschlistePosEigenschaft_arr[]      = $CWunschlistePosEigenschaft;
            }
            $CWunschlistePos->Artikel = new Artikel($CWunschlistePos->kArtikel);
            $CWunschlistePos->Artikel->fuelleArtikel($CWunschlistePos->kArtikel, $defaultOptions);
            $CWunschlistePos->cArtikelName = (strlen($CWunschlistePos->Artikel->cName) === 0)
                ? $cArtikelName
                : $CWunschlistePos->Artikel->cName;
            $this->CWunschlistePos_arr[] = $CWunschlistePos;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function ueberpruefePositionen()
    {
        $cArtikel_arr = [];
        $hinweis      = '';
        foreach ($this->CWunschlistePos_arr as $CWunschlistePos) {
            // Hat die Position einen Artikel
            if (!isset($CWunschlistePos->kArtikel) || (int)$CWunschlistePos->kArtikel <= 0) {
                continue;
            }
            // Prüfe auf kArtikel
            $oArtikelVorhanden = Shop::DB()->select('tartikel', 'kArtikel', (int)$CWunschlistePos->kArtikel);
            // Falls Artikel vorhanden
            if (isset($oArtikelVorhanden->kArtikel) && (int)$oArtikelVorhanden->kArtikel > 0) {
                // Sichtbarkeit Prüfen
                $oSichtbarkeit = Shop::DB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel', (int)$CWunschlistePos->kArtikel,
                    'kKundengruppe', Session::CustomerGroup()->getID()
                );
                if ($oSichtbarkeit === null || empty($oSichtbarkeit->kArtikel)) {
                    // Prüfe welche kEigenschaft gesetzt ist
                    if (count($CWunschlistePos->CWunschlistePosEigenschaft_arr) > 0) {
                        // Variationskombination?
                        if (ArtikelHelper::isVariChild($CWunschlistePos->kArtikel)) {
                            foreach ($CWunschlistePos->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft) {
                                $oEigenschaftWertVorhanden = Shop::DB()->select(
                                    'teigenschaftkombiwert',
                                    'kEigenschaftKombi', (int)$oArtikelVorhanden->kEigenschaftKombi,
                                    'kEigenschaftWert', (int)$CWunschlistePosEigenschaft->kEigenschaftWert,
                                    'kEigenschaft', (int)$CWunschlistePosEigenschaft->kEigenschaft,
                                    false,
                                    'kEigenschaftKombi'
                                );

                                // Prüfe ob die Eigenschaft vorhanden ist
                                if (empty($oEigenschaftWertVorhanden->kEigenschaftKombi)) {
                                    $cArtikel_arr[] = $CWunschlistePos->cArtikelName;
                                    $hinweis .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                    // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
                                    $this->delWunschlistePosSess($CWunschlistePos->kArtikel);
                                    break;
                                }
                            }
                        } else {
                            // Prüfe welche kEigenschaft gesetzt ist
                            $oEigenschaft_arr = Shop::DB()->selectAll(
                                'teigenschaft',
                                'kArtikel', (int)$CWunschlistePos->kArtikel,
                                'kEigenschaft, cName, cTyp'
                            );
                            if (count($oEigenschaft_arr) > 0) {
                                foreach ($CWunschlistePos->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft) {
                                    $oEigenschaftWertVorhanden = null;
                                    if (!empty($CWunschlistePosEigenschaft->kEigenschaft)) {
                                        $oEigenschaftWertVorhanden = Shop::DB()->select(
                                            'teigenschaftwert',
                                            'kEigenschaftWert',
                                            (int)$CWunschlistePosEigenschaft->kEigenschaftWert,
                                            'kEigenschaft',
                                            (int)$CWunschlistePosEigenschaft->kEigenschaft
                                        );
                                        if (empty($oEigenschaftWertVorhanden)) {
                                            $oEigenschaftWertVorhanden = Shop::DB()->select(
                                                'twunschlisteposeigenschaft',
                                                'kEigenschaft',
                                                $CWunschlistePosEigenschaft->kEigenschaft
                                            );
                                        }
                                    }
                                    // Prüfe ob die Eigenschaft vorhanden ist
                                    if (empty($oEigenschaftWertVorhanden->kEigenschaftWert) && empty($oEigenschaftWertVorhanden->cFreifeldWert)) {
                                        $cArtikel_arr[] = $CWunschlistePos->cArtikelName;
                                        $hinweis .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');

                                        // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
                                        $this->delWunschlistePosSess($CWunschlistePos->kArtikel);
                                        break;
                                    }
                                }
                            } else {
                                // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
                                $this->delWunschlistePosSess($CWunschlistePos->kArtikel);
                            }
                        }
                    }
                } else {
                    $cArtikel_arr[] = $CWunschlistePos->cArtikelName;
                    $hinweis .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                    // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
                    $this->delWunschlistePosSess($CWunschlistePos->kArtikel);
                }
            } else {
                $cArtikel_arr[] = $CWunschlistePos->cArtikelName;
                $hinweis .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
                $this->delWunschlistePosSess($CWunschlistePos->kArtikel);
            }
        }

        return $hinweis . implode(', ', $cArtikel_arr);
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public function delWunschlistePosSess($kArtikel)
    {
        $kArtikel = (int)$kArtikel;
        if (!$kArtikel) {
            return false;
        }
        // Positionen und Eigenschaften der Wunschliste welche nicht mehr Gültig sind in der Session durchgehen, löschen und unsetten
        foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $CWunschlistePosSESS) {
            if ($kArtikel === (int)$CWunschlistePosSESS->kArtikel) {
                unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
                Shop::DB()->delete('twunschlistepos', 'kWunschlistePos', (int)$CWunschlistePosSESS->kWunschlistePos);
                Shop::DB()->delete('twunschlisteposeigenschaft', 'kWunschlistePos', (int)$CWunschlistePosSESS->kWunschlistePos);
                break;
            }
        }

        return true;
    }

    /**
     * Holt alle Artikel mit der aktuellen Sprache bzw Waehrung aus der DB und weißt sie neu der Session zu
     *
     * @return $this
     */
    public function umgebungsWechsel()
    {
        if (count($_SESSION['Wunschliste']->CWunschlistePos_arr) > 0) {
            $defaultOptions = Artikel::getDefaultOptions();
            foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $oWunschlistePos) {
                $oArtikel = new Artikel();
                $oArtikel->fuelleArtikel($oWunschlistePos->kArtikel, $defaultOptions);
                $_SESSION['Wunschliste']->CWunschlistePos_arr[$i]->Artikel      = $oArtikel;
                $_SESSION['Wunschliste']->CWunschlistePos_arr[$i]->cArtikelName = $oArtikel->cName;
            }
        }

        return $this;
    }
}
