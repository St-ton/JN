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
    public function fuegeEin(int $kArtikel, string $cArtikelName, array $oEigenschaftwerte_arr, $fAnzahl): int
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

        return (int)$kWunschlistePos;
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

            $wlPosition->cPreis = Preise::getLocalizedPriceString($fPreis, Session::Currency());
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

    /**
     * Überprüft Parameter und gibt falls erfolgreich kWunschliste zurück, ansonten 0
     *
     * @return int
     * @former checkeWunschlisteParameter()
     * @since 5.0.0
     */
    public static function checkeParameters(): int
    {
        $cURLID = StringHandler::filterXSS(RequestHelper::verifyGPDataString('wlid'));

        if (strlen($cURLID) > 0) {
            $campaing = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
            $id       = $campaing->kKampagne > 0
                ? ($cURLID . '&' . $campaing->cParameter . '=' . $campaing->cWert)
                : $cURLID;
            $keys     = ['nOeffentlich', 'cURLID'];
            $values   = [1, $id];
            $wishList = Shop::Container()->getDB()->select('twunschliste', $keys, $values);

            if ($wishList !== null && $wishList->kWunschliste > 0) {
                return (int)$wishList->kWunschliste;
            }
        }

        return 0;
    }
    /**
     * Holt für einen Kunden die aktive Wunschliste (falls vorhanden) aus der DB und fügt diese in die Session
     */
    public static function persistInSession()
    {
        if (!empty($_SESSION['Kunde']->kKunde)) {
            $oWunschliste = Shop::Container()->getDB()->select(
                'twunschliste',
                ['kKunde', 'nStandard'],
                [(int)$_SESSION['Kunde']->kKunde, 1]
            );
            if (isset($oWunschliste->kWunschliste)) {
                $_SESSION['Wunschliste'] = new Wunschliste($oWunschliste->kWunschliste);
                $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();
            }
        }
    }

    /**
     * @param int $id
     * @return string
     */
    public static function delete(int $id): string
    {
        $msg = '';
        if ($id === 0) {
            return $msg;
        }
        // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $id);
        $customer     = Session::Customer();
        if (isset($oWunschliste->kKunde) && (int)$oWunschliste->kKunde === $customer->getID()) {
            // Hole alle Positionen der Wunschliste
            $oWunschlistePos_arr = Shop::Container()->getDB()->selectAll(
                'twunschlistepos',
                'kWunschliste',
                $id,
                'kWunschlistePos'
            );
            // Alle Eigenschaften und Positionen aus DB löschen
            foreach ($oWunschlistePos_arr as $oWunschlistePos) {
                Shop::Container()->getDB()->delete(
                    'twunschlisteposeigenschaft',
                    'kWunschlistePos',
                    $oWunschlistePos->kWunschlistePos
                );
            }
            // Lösche alle Positionen mit $id
            Shop::Container()->getDB()->delete('twunschlistepos', 'kWunschliste', $id);
            // Lösche Wunschliste aus der DB
            Shop::Container()->getDB()->delete('twunschliste', 'kWunschliste', $id);
            // Lösche Wunschliste aus der Session (falls Wunschliste = Standard)
            if (isset($_SESSION['Wunschliste']->kWunschliste)
                && (int)$_SESSION['Wunschliste']->kWunschliste === $id
            ) {
                unset($_SESSION['Wunschliste']);
            }
            // Wenn die gelöschte Wunschliste nStandard = 1 war => neue setzen
            if ((int)$oWunschliste->nStandard === 1) {
                // Neue Wunschliste holen (falls vorhanden) und nStandard=1 neu setzen
                $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kKunde', $customer->getID());
                if (isset($oWunschliste->kWunschliste)) {
                    Shop::Container()->getDB()->query(
                        'UPDATE twunschliste 
                            SET nStandard = 1 
                            WHERE kWunschliste = ' . (int)$oWunschliste->kWunschliste,
                        \DB\ReturnType::AFFECTED_ROWS
                    );
                    // Neue Standard Wunschliste in die Session laden
                    $_SESSION['Wunschliste'] = new Wunschliste($oWunschliste->kWunschliste);
                    $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();
                }
            }

            $msg = Shop::Lang()->get('wishlistDelete', 'messages');
        }

        return $msg;
    }

    /**
     * @param int $id
     * @return string
     */
    public static function update(int $id): string
    {
        $msg = '';
        if (isset($_POST['WunschlisteName']) && strlen($_POST['WunschlisteName']) > 0) {
            $cName = StringHandler::htmlentities(StringHandler::filterXSS(substr($_POST['WunschlisteName'], 0, 254)));
            Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $id, (object)['cName' => $cName]);
        }
        // aktualisiere Positionen
        $oWunschlistePos_arr = Shop::Container()->getDB()->selectAll(
            'twunschlistepos',
            'kWunschliste',
            $id,
            'kWunschlistePos'
        );
        // Prüfen ab Positionen vorhanden
        if (count($oWunschlistePos_arr) > 0) {
            foreach ($oWunschlistePos_arr as $oWunschlistePos) {
                $kWunschlistePos = (int)$oWunschlistePos->kWunschlistePos;
                // Ist ein Kommentar vorhanden
                if (strlen($_POST['Kommentar_' . $kWunschlistePos]) > 0) {
                    $cKommentar = substr($_POST['Kommentar_' . $kWunschlistePos], 0, 254);
                    // Kommentar der Position updaten
                    $_upd             = new stdClass();
                    $_upd->cKommentar = StringHandler::htmlentities(
                        StringHandler::filterXSS(Shop::Container()->getDB()->escape($cKommentar))
                    );
                    Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $kWunschlistePos, $_upd);
                }
                // Ist eine Anzahl gesezt
                if ((int)$_POST['Anzahl_' . $kWunschlistePos] > 0) {
                    $fAnzahl = (float)$_POST['Anzahl_' . $kWunschlistePos];
                    // Anzahl der Position updaten
                    $_upd          = new stdClass();
                    $_upd->fAnzahl = $fAnzahl;
                    Shop::Container()->getDB()->update('twunschlistepos', 'kWunschlistePos', $kWunschlistePos, $_upd);
                }
            }
            $msg = Shop::Lang()->get('wishlistUpdate', 'messages');
        }

        return $msg;
    }

    /**
     * @param int $id
     * @return string
     */
    public static function setDefault(int $id): string
    {
        $msg = '';
        if ($id === 0) {
            return $msg;
        }
        // Prüfe ob die Wunschliste dem eingeloggten Kunden gehört
        $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $id);
        if ($oWunschliste !== null && (int)$oWunschliste->kKunde === Session::Customer()->getID()) {
            // Wunschliste auf Standard setzen
            Shop::Container()->getDB()->update(
                'twunschliste',
                'kKunde',
                (int)$_SESSION['Kunde']->kKunde,
                (object)['nStandard' => 0]
            );
            Shop::Container()->getDB()->update(
                'twunschliste',
                'kWunschliste',
                $id,
                (object)['nStandard' => 1]
            );
            // Session updaten
            unset($_SESSION['Wunschliste']);
            $_SESSION['Wunschliste'] = new Wunschliste($id);
            $GLOBALS['hinweis']      = $_SESSION['Wunschliste']->ueberpruefePositionen();

            $msg = Shop::Lang()->get('wishlistStandard', 'messages');
        }

        return $msg;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function save(string $name): string
    {
        $msg = '';
        if ($_SESSION['Kunde']->kKunde > 0 && !empty($name)) {
            $list            = new Wunschliste();
            $list->cName     = $name;
            $list->nStandard = 0;
            unset(
                $list->CWunschlistePos_arr,
                $list->oKunde,
                $list->kWunschliste,
                $list->dErstellt_DE
            );

            Shop::Container()->getDB()->insert('twunschliste', $list);

            $msg = Shop::Lang()->get('wishlistAdd', 'messages');
        }

        return $msg;
    }

    /**
     * @param array $recipients
     * @param int   $id
     * @return string
     */
    public static function send(array $recipients, int $id): string
    {
        $msg = '';
        // Wurden Emails übergeben?
        if (count($recipients) > 0) {
            $conf                = Shop::getSettings([CONF_GLOBAL]);
            $oMail               = new stdClass();
            $oMail->tkunde       = $_SESSION['Kunde'];
            $oMail->twunschliste = self::buildPrice(new Wunschliste($id));

            $oWunschlisteVersand                    = new stdClass();
            $oWunschlisteVersand->kWunschliste      = $id;
            $oWunschlisteVersand->dZeit             = 'now()';
            $oWunschlisteVersand->nAnzahlEmpfaenger = min(
                count($recipients),
                (int)$conf['global']['global_wunschliste_max_email']
            );
            $oWunschlisteVersand->nAnzahlArtikel    = count($oMail->twunschliste->CWunschlistePos_arr);

            Shop::Container()->getDB()->insert('twunschlisteversand', $oWunschlisteVersand);

            $cValidEmail_arr = [];
            // Schleife mit Emails (versenden)
            for ($i = 0; $i < $oWunschlisteVersand->nAnzahlEmpfaenger; $i++) {
                // Email auf "Echtheit" prüfen
                $cEmail = StringHandler::filterXSS($recipients[$i]);
                if (!SimpleMail::checkBlacklist($cEmail)) {
                    $oMail->mail          = new stdClass();
                    $oMail->mail->toEmail = $cEmail;
                    $oMail->mail->toName  = $cEmail;
                    // Emails senden
                    sendeMail(MAILTEMPLATE_WUNSCHLISTE, $oMail);
                } else {
                    $cValidEmail_arr[] = $cEmail;
                }
            }
            // Gabs Emails die nicht validiert wurden?
            if (count($cValidEmail_arr) > 0) {
                $msg = Shop::Lang()->get('novalidEmail', 'messages');
                foreach ($cValidEmail_arr as $cValidEmail) {
                    $msg .= $cValidEmail . ', ';
                }
                $msg = substr($msg, 0, strlen($msg) - 2) . '<br />';
            }
            // Hat der benutzer mehr Emails angegeben als erlaubt sind?
            if (count($recipients) > (int)$conf['global']['global_wunschliste_max_email']) {
                $nZuviel = count($recipients) - (int)$conf['global']['global_wunschliste_max_email'];
                $msg .= '<br />';

                if (strpos($msg, Shop::Lang()->get('novalidEmail', 'messages')) === false) {
                    $msg = Shop::Lang()->get('novalidEmail', 'messages');
                }

                for ($i = 0; $i < $nZuviel; $i++) {
                    if (strpos($msg, $recipients[(count($recipients) - 1) - $i]) === false) {
                        if ($i > 0) {
                            $msg .= ', ' . $recipients[(count($recipients) - 1) - $i];
                        } else {
                            $msg .= $recipients[(count($recipients) - 1) - $i];
                        }
                    }
                }

                $msg .= '<br />';
            }

            $msg .= Shop::Lang()->get('emailSeccessfullySend', 'messages');
        } else {
            // Keine Emails eingegeben
            $msg = Shop::Lang()->get('noEmail', 'messages');
        }

        return $msg;
    }

    /**
     * @param int $wishListID
     * @param int $wishListPositionID
     * @return array|bool
     */
    public static function getAttributesByID(int $wishListID, int $wishListPositionID)
    {
        if ($wishListID > 0 && $wishListPositionID > 0) {
            // $oEigenschaftwerte_arr anlegen
            $data                           = [];
            $oWunschlistePosEigenschaft_arr = Shop::Container()->getDB()->selectAll(
                'twunschlisteposeigenschaft',
                'kWunschlistePos',
                $wishListPositionID
            );
            foreach ($oWunschlistePosEigenschaft_arr as $oWunschlistePosEigenschaft) {
                $value                       = new stdClass();
                $value->kEigenschaftWert     = $oWunschlistePosEigenschaft->kEigenschaftWert;
                $value->kEigenschaft         = $oWunschlistePosEigenschaft->kEigenschaft;
                $value->cEigenschaftName     = $oWunschlistePosEigenschaft->cEigenschaftName;
                $value->cEigenschaftWertName = $oWunschlistePosEigenschaft->cEigenschaftWertName;
                $value->cFreifeldWert        = $oWunschlistePosEigenschaft->cFreifeldWert;

                $data[] = $value;
            }

            return $data;
        }

        return false;
    }

    /**
     * @param int $id
     * @return object|bool
     */
    public static function getWishListPositionDataByID(int $id)
    {
        if ($id > 0) {
            $pos = Shop::Container()->getDB()->select('twunschlistepos', 'kWunschlistePos', $id);
            if (!empty($pos->kWunschliste)) {
                $oArtikel = new Artikel();
                $oArtikel->fuelleArtikel($pos->kArtikel, Artikel::getDefaultOptions());

                if ($oArtikel->kArtikel > 0) {
                    $pos->bKonfig = $oArtikel->bHasKonfig;
                }

                return $pos;
            }
        }

        return false;
    }

    /**
     * @param int    $id
     * @param string $cURLID
     * @return bool|stdClass
     */
    public static function getWishListDataByID(int $id = 0, string $cURLID = '')
    {
        $oWunschliste = null;
        if ($id > 0) {
            $oWunschliste = Shop::Container()->getDB()->select('twunschliste', 'kWunschliste', $id);
        } elseif ($cURLID !== '') {
            $oWunschliste = Shop::Container()->getDB()->executeQueryPrepared(
                "SELECT * FROM twunschliste WHERE cURLID LIKE :id",
                ['id' => $cURLID],
                \DB\ReturnType::SINGLE_OBJECT
            );
        }
        return (isset($oWunschliste->kWunschliste) && $oWunschliste->kWunschliste > 0)
            ? $oWunschliste
            : false;
    }

    /**
     * @param Wunschliste $wishList
     * @return Wunschliste
     */
    public static function buildPrice(Wunschliste $wishList): Wunschliste
    {
        // Wunschliste durchlaufen und cPreis setzen (Artikelanzahl mit eingerechnet)
        if (is_array($wishList->CWunschlistePos_arr) && count($wishList->CWunschlistePos_arr) > 0) {
            foreach ($wishList->CWunschlistePos_arr as $wishListPos) {
                if (Session::CustomerGroup()->isMerchant()) {
                    $fPreis = isset($wishListPos->Artikel->Preise->fVKNetto)
                        ? (int)$wishListPos->fAnzahl * $wishListPos->Artikel->Preise->fVKNetto
                        : 0;
                } else {
                    $fPreis = isset($wishListPos->Artikel->Preise->fVKNetto)
                        ? (int)$wishListPos->fAnzahl *
                        (
                            $wishListPos->Artikel->Preise->fVKNetto *
                            (100 + $_SESSION['Steuersatz'][$wishListPos->Artikel->kSteuerklasse]) / 100
                        )
                        : 0;
                }
                $wishListPos->cPreis = Preise::getLocalizedPriceString($fPreis, Session::Currency());
            }
        }

        return $wishList;
    }

    /**
     * @param int $nMSGCode
     * @return string
     */
    public static function mapMessage(int $nMSGCode): string
    {
        $cMSG = '';
        switch ($nMSGCode) {
            case 1:
                $cMSG = Shop::Lang()->get('basketAdded', 'messages');
                break;
            case 2:
                $cMSG = Shop::Lang()->get('basketAllAdded', 'messages');
                break;
            default:
                break;
        }

        return $cMSG;
    }

    /**
     * @param int $wishlistID
     */
    public static function setPrivate(int $wishlistID)
    {
        $upd               = new stdClass();
        $upd->nOeffentlich = 0;
        $upd->cURLID       = '';
        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $wishlistID, $upd);
    }

    /**
     * @param int $wishlistID
     */
    public static function setPublic(int $wishlistID)
    {
        $URLID = uniqid('', true);

        $campaign = new Kampagne(KAMPAGNE_INTERN_OEFFENTL_WUNSCHZETTEL);
        if ($campaign->kKampagne > 0) {
            $URLID .= '&' . $campaign->cParameter . '=' . $campaign->cWert;
        }
        $upd               = new stdClass();
        $upd->nOeffentlich = 1;
        $upd->cURLID       = $URLID;
        Shop::Container()->getDB()->update('twunschliste', 'kWunschliste', $wishlistID, $upd);
    }

}
