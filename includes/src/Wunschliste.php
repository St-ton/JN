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
    public function __construct(int $kWunschliste = 0)
    {
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
    public function fuegeEin(int $kArtikel, string $cArtikelName, array $oEigenschaftwerte_arr, $fAnzahl)
    {
        $bBereitsEnthalten = false;
        $nPosition         = 0;
        foreach ($this->CWunschlistePos_arr as $i => $wlPosition) {
            $wlPosition->kArtikel = (int)$wlPosition->kArtikel;
            if ($bBereitsEnthalten) {
                break;
            }

            if ($wlPosition->kArtikel === $kArtikel) {
                $nPosition         = $i;
                $bBereitsEnthalten = true;
                if (count($wlPosition->CWunschlistePosEigenschaft_arr) > 0) {
                    foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                        if (!$wlPosition->istEigenschaftEnthalten(
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
            $wlPosition                = new WunschlistePos($kArtikel, $cArtikelName, $fAnzahl,
                $this->kWunschliste);
            $wlPosition->dHinzugefuegt = date('Y-m-d H:i:s');
            $wlPosition->schreibeDB();
            $kWunschlistePos = $wlPosition->kWunschlistePos;
            $wlPosition->erstellePosEigenschaften($oEigenschaftwerte_arr);
            $CArtikel = new Artikel();
            $CArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            $wlPosition->Artikel         = $CArtikel;
            $this->CWunschlistePos_arr[] = $wlPosition;
        }

        executeHook(HOOK_WUNSCHLISTE_CLASS_FUEGEEIN);

        return $kWunschlistePos;
    }

    /**
     * @param int $kWunschlistePos
     * @return $this
     */
    public function entfernePos(int $kWunschlistePos): self
    {
        $oKunde = Shop::Container()->getDB()->queryPrepared(
            'SELECT twunschliste.kKunde
                FROM twunschliste
                JOIN twunschlistepos 
                    ON twunschliste.kWunschliste = twunschlistepos.kWunschliste
                WHERE twunschlistepos.kWunschlistePos = :wlID',
            ['wlID' => $kWunschlistePos],
            \DB\ReturnType::SINGLE_OBJECT
        );

        // Prüfen ob der eingeloggte Kunde auch der Besitzer der zu löschenden WunschlistenPos ist
        if (!empty($oKunde->kKunde) && $oKunde->kKunde == $_SESSION['Kunde']->kKunde) {
            // Alle Eigenschaften löschen
            Shop::Container()->getDB()->delete('twunschlisteposeigenschaft', 'kWunschlistePos', $kWunschlistePos);

            // Die Posiotion mit ID $kWunschlistePos löschen
            Shop::Container()->getDB()->delete('twunschlistepos', 'kWunschlistePos', $kWunschlistePos);

            // Wunschliste Position aus der Session löschen
            foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $wlPosition) {
                if ($wlPosition->kWunschlistePos == $kWunschlistePos) {
                    unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                }
            }

            // Positionen Array in der Wunschliste neu nummerieren
            $_SESSION['Wunschliste']->CWunschlistePos_arr = array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function entferneAllePos(): int
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE twunschlistepos, twunschlisteposeigenschaft 
                FROM twunschlistepos
                LEFT JOIN twunschlisteposeigenschaft 
                    ON twunschlisteposeigenschaft.kWunschlistePos = twunschlistepos.kWunschlistePos
                WHERE twunschlistepos.kWunschliste = :wlID',
            ['wlID' => (int)$this->kWunschliste],
            \DB\ReturnType::AFFECTED_ROWS
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
    public static function pruefeArtikelnachBestellungLoeschen(int $kWunschliste, array $oWarenkorbpositionen_arr)
    {
        $conf = Shop::getSettings([CONF_GLOBAL]);
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
                    foreach ($oWunschlistePos->CWunschlistePosEigenschaft_arr as $oWPEigenschaft) {
                        if ($index === $nMatchesFound) {
                            foreach ($oArtikel->WarenkorbPosEigenschaftArr as $oAEigenschaft) {
                                if ($oWPEigenschaft->kEigenschaftWert != 0
                                    && $oWPEigenschaft->kEigenschaftWert === $oAEigenschaft->kEigenschaftWert
                                ) {
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
    public function sucheInWunschliste(string $cSuche): array
    {
        if (empty($cSuche)) {
            return [];
        }
        $searchResults     = [];
        $oSuchergebnis_arr = Shop::Container()->getDB()->queryPrepared(
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
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($oSuchergebnis_arr as $i => $oSuchergebnis) {
            $wlPosition = new WunschlistePos(
                $oSuchergebnis->kArtikel,
                $oSuchergebnis->cArtikelName,
                $oSuchergebnis->fAnzahl,
                $oSuchergebnis->kWunschliste
            );

            $wlPosition->kWunschlistePos  = $oSuchergebnis->kWunschlistePos;
            $wlPosition->cKommentar       = $oSuchergebnis->cKommentar;
            $wlPosition->dHinzugefuegt    = $oSuchergebnis->dHinzugefuegt;
            $wlPosition->dHinzugefuegt_de = $oSuchergebnis->dHinzugefuegt_de;

            $wlPositionAttributes = Shop::Container()->getDB()->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*, teigenschaftsprache.cName
                    FROM twunschlisteposeigenschaft
                    JOIN teigenschaftsprache 
                        ON teigenschaftsprache.kEigenschaft = twunschlisteposeigenschaft.kEigenschaft
                    WHERE twunschlisteposeigenschaft.kWunschlistePos = :wlID
                    GROUP BY twunschlisteposeigenschaft.kWunschlistePosEigenschaft',
                ['wlID' => (int)$oSuchergebnis->kWunschlistePos],
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($wlPositionAttributes as $wlPositionAttribute) {
                if (strlen($wlPositionAttribute->cFreifeldWert) > 0) {
                    $wlPositionAttribute->cEigenschaftName     = $wlPositionAttribute->cName;
                    $wlPositionAttribute->cEigenschaftWertName = $wlPositionAttribute->cFreifeldWert;
                }
                $wlAttribute = new WunschlistePosEigenschaft(
                    $wlPositionAttribute->kEigenschaft,
                    $wlPositionAttribute->kEigenschaftWert,
                    $wlPositionAttribute->cFreifeldWert,
                    $wlPositionAttribute->cEigenschaftName,
                    $wlPositionAttribute->cEigenschaftWertName,
                    $wlPositionAttribute->kWunschlistePos
                );

                $wlAttribute->kWunschlistePosEigenschaft = $wlPositionAttribute->kWunschlistePosEigenschaft;

                $wlPosition->CWunschlistePosEigenschaft_arr[] = $wlAttribute;
            }

            $wlPosition->Artikel = new Artikel();
            $wlPosition->Artikel->fuelleArtikel($oSuchergebnis->kArtikel, Artikel::getDefaultOptions());
            $wlPosition->cArtikelName = $wlPosition->Artikel->cName;

            if (Session::CustomerGroup()->isMerchant()) {
                $fPreis = (int)$wlPosition->fAnzahl *
                    $wlPosition->Artikel->Preise->fVKNetto;
            } else {
                $fPreis = (int)$wlPosition->fAnzahl *
                    ($wlPosition->Artikel->Preise->fVKNetto *
                        (100 + $_SESSION['Steuersatz'][$wlPosition->Artikel->kSteuerklasse]) /
                        100);
            }

            $wlPosition->cPreis = gibPreisStringLocalized($fPreis, Session::Currency());
            $searchResults[$i]  = $wlPosition;
        }

        return $searchResults;
    }

    /**
     * @return $this
     */
    public function schreibeDB(): self
    {
        $oTemp               = new stdClass();
        $oTemp->kKunde       = $this->kKunde;
        $oTemp->cName        = $this->cName;
        $oTemp->nStandard    = $this->nStandard;
        $oTemp->nOeffentlich = $this->nOeffentlich;
        $oTemp->dErstellt    = $this->dErstellt;
        $oTemp->cURLID       = $this->cURLID;

        $this->kWunschliste = Shop::Container()->getDB()->insert('twunschliste', $oTemp);

        return $this;
    }

    /**
     * @return $this
     */
    public function ladeWunschliste(): self
    {
        // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
        $oWunschliste       = Shop::Container()->getDB()->queryPrepared(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i') AS dErstellt_DE
                FROM twunschliste
                WHERE kWunschliste = :wlID",
            ['wlID' => (int)$this->kWunschliste],
            \DB\ReturnType::SINGLE_OBJECT
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
        $wlPositions    = Shop::Container()->getDB()->selectAll(
            'twunschlistepos',
            'kWunschliste',
            (int)$this->kWunschliste,
            '*, date_format(dHinzugefuegt, \'%d.%m.%Y %H:%i\') AS dHinzugefuegt_de'
        );
        $defaultOptions = Artikel::getDefaultOptions();
        // Hole alle Eigenschaften für eine Position
        foreach ($wlPositions as $WunschlistePos) {
            $wlPosition = new WunschlistePos(
                $WunschlistePos->kArtikel,
                $WunschlistePos->cArtikelName,
                $WunschlistePos->fAnzahl,
                $WunschlistePos->kWunschliste
            );

            $cArtikelName                 = $wlPosition->cArtikelName;
            $wlPosition->kWunschlistePos  = (int)$WunschlistePos->kWunschlistePos;
            $wlPosition->cKommentar       = $WunschlistePos->cKommentar;
            $wlPosition->dHinzugefuegt    = $WunschlistePos->dHinzugefuegt;
            $wlPosition->dHinzugefuegt_de = $WunschlistePos->dHinzugefuegt_de;

            $wlPositionAttributes = Shop::Container()->getDB()->queryPrepared(
                'SELECT twunschlisteposeigenschaft.*, 
                    IF(LENGTH(teigenschaftsprache.cName) > 0, 
                        teigenschaftsprache.cName, 
                        twunschlisteposeigenschaft.cEigenschaftName) AS cName,
                    IF(LENGTH(teigenschaftwertsprache.cName) > 0, 
                        teigenschaftwertsprache.cName, 
                        twunschlisteposeigenschaft.cEigenschaftWertName) AS cWert
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
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($wlPositionAttributes as $wlPositionAttribute) {
                if (strlen($wlPositionAttribute->cFreifeldWert) > 0) {
                    if (empty($wlPositionAttribute->cName)) {
                        $_cName                     = Shop::Container()->getDB()->queryPrepared(
                            "SELECT IF(LENGTH(teigenschaftsprache.cName) > 0, 
                                teigenschaftsprache.cName, 
                                teigenschaft.cName) AS cName
                                FROM teigenschaft
                                LEFT JOIN teigenschaftsprache 
                                    ON teigenschaftsprache.kEigenschaft = teigenschaft.kEigenschaft
                                    AND teigenschaftsprache.kSprache = :langID
                                WHERE teigenschaft.kEigenschaft = :attrID",
                            [
                                'langID' => $langID,
                                'attrID' => (int)$wlPositionAttribute->kEigenschaft
                            ],
                            \DB\ReturnType::SINGLE_OBJECT
                        );
                        $wlPositionAttribute->cName = $_cName->cName;
                    }
                    $wlPositionAttribute->cWert = $wlPositionAttribute->cFreifeldWert;
                }

                $wlAttribute = new WunschlistePosEigenschaft(
                    $wlPositionAttribute->kEigenschaft,
                    $wlPositionAttribute->kEigenschaftWert,
                    $wlPositionAttribute->cFreifeldWert,
                    $wlPositionAttribute->cName,
                    $wlPositionAttribute->cWert,
                    $wlPositionAttribute->kWunschlistePos);

                $wlAttribute->kWunschlistePosEigenschaft      = (int)$wlPositionAttribute->kWunschlistePosEigenschaft;
                $wlPosition->CWunschlistePosEigenschaft_arr[] = $wlAttribute;
            }
            $wlPosition->Artikel = new Artikel($wlPosition->kArtikel);
            $wlPosition->Artikel->fuelleArtikel($wlPosition->kArtikel, $defaultOptions);
            $wlPosition->cArtikelName    = strlen($wlPosition->Artikel->cName) === 0
                ? $cArtikelName
                : $wlPosition->Artikel->cName;
            $this->CWunschlistePos_arr[] = $wlPosition;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function ueberpruefePositionen(): string
    {
        $cArtikel_arr = [];
        $hinweis      = '';
        foreach ($this->CWunschlistePos_arr as $wlPosition) {
            // Hat die Position einen Artikel
            if (!isset($wlPosition->kArtikel) || (int)$wlPosition->kArtikel <= 0) {
                continue;
            }
            // Prüfe auf kArtikel
            $oArtikelVorhanden = Shop::Container()->getDB()->select('tartikel', 'kArtikel', $wlPosition->kArtikel);
            // Falls Artikel vorhanden
            if (isset($oArtikelVorhanden->kArtikel) && (int)$oArtikelVorhanden->kArtikel > 0) {
                // Sichtbarkeit Prüfen
                $oSichtbarkeit = Shop::Container()->getDB()->select(
                    'tartikelsichtbarkeit',
                    'kArtikel', (int)$wlPosition->kArtikel,
                    'kKundengruppe', Session::CustomerGroup()->getID()
                );
                if ($oSichtbarkeit === null || empty($oSichtbarkeit->kArtikel)) {
                    // Prüfe welche kEigenschaft gesetzt ist
                    if (count($wlPosition->CWunschlistePosEigenschaft_arr) > 0) {
                        // Variationskombination?
                        if (ArtikelHelper::isVariChild($wlPosition->kArtikel)) {
                            foreach ($wlPosition->CWunschlistePosEigenschaft_arr as $wlAttribute) {
                                $oEigenschaftWertVorhanden = Shop::Container()->getDB()->select(
                                    'teigenschaftkombiwert',
                                    'kEigenschaftKombi', (int)$oArtikelVorhanden->kEigenschaftKombi,
                                    'kEigenschaftWert', (int)$wlAttribute->kEigenschaftWert,
                                    'kEigenschaft', (int)$wlAttribute->kEigenschaft,
                                    false,
                                    'kEigenschaftKombi'
                                );

                                // Prüfe ob die Eigenschaft vorhanden ist
                                if (empty($oEigenschaftWertVorhanden->kEigenschaftKombi)) {
                                    $cArtikel_arr[] = $wlPosition->cArtikelName;
                                    $hinweis        .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                                    $this->delWunschlistePosSess($wlPosition->kArtikel);
                                    break;
                                }
                            }
                        } else {
                            // Prüfe welche kEigenschaft gesetzt ist
                            $oEigenschaft_arr = Shop::Container()->getDB()->selectAll(
                                'teigenschaft',
                                'kArtikel', (int)$wlPosition->kArtikel,
                                'kEigenschaft, cName, cTyp'
                            );
                            if (count($oEigenschaft_arr) > 0) {
                                foreach ($wlPosition->CWunschlistePosEigenschaft_arr as $wlAttribute) {
                                    $oEigenschaftWertVorhanden = null;
                                    if (!empty($wlAttribute->kEigenschaft)) {
                                        $oEigenschaftWertVorhanden = Shop::Container()->getDB()->select(
                                            'teigenschaftwert',
                                            'kEigenschaftWert',
                                            (int)$wlAttribute->kEigenschaftWert,
                                            'kEigenschaft',
                                            (int)$wlAttribute->kEigenschaft
                                        );
                                        if (empty($oEigenschaftWertVorhanden)) {
                                            $oEigenschaftWertVorhanden = Shop::Container()->getDB()->select(
                                                'twunschlisteposeigenschaft',
                                                'kEigenschaft',
                                                $wlAttribute->kEigenschaft
                                            );
                                        }
                                    }
                                    // Prüfe ob die Eigenschaft vorhanden ist
                                    if (empty($oEigenschaftWertVorhanden->kEigenschaftWert)
                                        && empty($oEigenschaftWertVorhanden->cFreifeldWert)
                                    ) {
                                        $cArtikel_arr[] = $wlPosition->cArtikelName;
                                        $hinweis        .= '<br />' . Shop::Lang()->get('noProductWishlist',
                                                'messages');

                                        $this->delWunschlistePosSess($wlPosition->kArtikel);
                                        break;
                                    }
                                }
                            } else {
                                $this->delWunschlistePosSess($wlPosition->kArtikel);
                            }
                        }
                    }
                } else {
                    $cArtikel_arr[] = $wlPosition->cArtikelName;
                    $hinweis        .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                    $this->delWunschlistePosSess($wlPosition->kArtikel);
                }
            } else {
                $cArtikel_arr[] = $wlPosition->cArtikelName;
                $hinweis        .= '<br />' . Shop::Lang()->get('noProductWishlist', 'messages');
                $this->delWunschlistePosSess($wlPosition->kArtikel);
            }
        }

        return $hinweis . implode(', ', $cArtikel_arr);
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public function delWunschlistePosSess(int $kArtikel): bool
    {
        if (!$kArtikel) {
            return false;
        }
        foreach ($_SESSION['Wunschliste']->CWunschlistePos_arr as $i => $CWunschlistePosSESS) {
            if ($kArtikel === (int)$CWunschlistePosSESS->kArtikel) {
                unset($_SESSION['Wunschliste']->CWunschlistePos_arr[$i]);
                array_merge($_SESSION['Wunschliste']->CWunschlistePos_arr);
                Shop::Container()->getDB()->delete(
                    'twunschlistepos',
                    'kWunschlistePos',
                    (int)$CWunschlistePosSESS->kWunschlistePos
                );
                Shop::Container()->getDB()->delete(
                    'twunschlisteposeigenschaft',
                    'kWunschlistePos',
                    (int)$CWunschlistePosSESS->kWunschlistePos
                );
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
    public function umgebungsWechsel(): self
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
