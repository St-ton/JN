<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Warenkorb
 */
class Warenkorb
{
    /**
     * @var int
     */
    public $kWarenkorb;

    /**
     * @var int
     */
    public $kKunde;

    /**
     * @var int
     */
    public $kLieferadresse;

    /**
     * @var int
     */
    public $kZahlungsInfo = 0;

    /**
     * @var array
     */
    public $PositionenArr = array();

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var array
     */
    public static $updatedPositions = array();

    /**
     * @var array
     */
    public static $deletedPositions = array();

    /**
     * Konstruktor
     *
     * @param int $kWarenkorb Falls angegeben, wird der Warenkorb mit angegebenem kWarenkorb aus der DB geholt
     * @return Warenkorb
     */
    public function __construct($kWarenkorb = 0)
    {
        $kWarenkorb = intval($kWarenkorb);
        if ($kWarenkorb > 0) {
            $this->loadFromDB($kWarenkorb);
        }
    }

    /**
     * Entfernt Positionen, die in der Wawi zwischenzeitlich deaktiviert/geloescht wurden
     * @return $this
     */
    public function loescheDeaktiviertePositionen()
    {
        $conf = Shop::getConfig(array(CONF_GLOBAL));
        foreach ($this->PositionenArr as $i => $Position) {
            $delete = false;
            if (!empty($Position->Artikel)) {
                if (isset($Position->Artikel->fLagerbestand) && isset($Position->Artikel->cLagerBeachten) && isset($Position->Artikel->cLagerKleinerNull) &&
                    isset($Position->Artikel->cLagerVariation) && $Position->Artikel->fLagerbestand <= 0 && $Position->Artikel->cLagerBeachten === 'Y' &&
                    $Position->Artikel->cLagerKleinerNull !== 'Y' && $Position->Artikel->cLagerVariation !== 'Y') {
                    $delete = true;
                } elseif (isset($Position->fPreisEinzelNetto) && $Position->fPreisEinzelNetto == 0 &&
                    isset($conf['global']['global_preis0']) && $conf['global']['global_preis0'] === 'N') {
                    $delete = true;
                } elseif (isset($Position->Artikel->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH]) &&
                    $Position->Artikel->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH]) {
                    $delete = true;
                } else {
                    $delete = (Shop::DB()->select('tartikel', 'kArtikel', $Position->kArtikel) === null);
                }
            }
            if ($delete) {
                self::addDeletedPosition($Position);
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = array_merge($this->PositionenArr);

        return $this;
    }

    /**
     * @param object $position
     */
    public static function addUpdatedPosition($position)
    {
        self::$updatedPositions[] = $position;
    }

    /**
     * @param object $position
     */
    public static function addDeletedPosition($position)
    {
        self::$deletedPositions[] = $position;
    }

    /**
     * fuegt eine neue Position hinzu
     *
     * @param int   $kArtikel ArtikelKey
     * @param int   $anzahl Anzahl des Artikel fuer die neue Position
     * @param array $oEigenschaftwerte_arr
     * @param int   $nPosTyp
     * @param bool  $cUnique
     * @param int   $kKonfigitem
     * @param bool  $setzePositionsPreise
     * @return $this
     */
    public function fuegeEin($kArtikel, $anzahl, $oEigenschaftwerte_arr, $nPosTyp = 1, $cUnique = false, $kKonfigitem = 0, $setzePositionsPreise = true)
    {
        //toDo schaue, ob diese Pos nicht markiert werden muesste, wenn anzahl>lager gekauft wird
        //schaue, ob es nicht schon Positionen mit diesem Artikel gibt
        foreach ($this->PositionenArr as $i => $Position) {
            if (isset($Position->Artikel->kArtikel) &&
                $Position->Artikel->kArtikel == $kArtikel &&
                $Position->nPosTyp == $nPosTyp &&
                !$Position->cUnique
            ) {
                $neuePos = false;
                //hat diese Position schon einen EigenschaftWert ausgewaehlt und ist das dieselbe eigenschaft wie ausgewaehlt?
                foreach ($Position->WarenkorbPosEigenschaftArr as $WKEigenschaft) {
                    foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                        //gleiche Eigenschaft suchen
                        if ($oEigenschaftwerte->kEigenschaft == $WKEigenschaft->kEigenschaft) {
                            //ist es ein Freifeld mit unterschieldichem Inhalt oder eine Eigenschaft mit unterschielichem Wert?
                            if ((($WKEigenschaft->cTyp === 'FREIFELD' || $WKEigenschaft->cTyp === 'PFLICHT-FREIFELD') &&
                                    $WKEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']] != $oEigenschaftwerte->cFreifeldWert)
                                ||
                                ($WKEigenschaft->kEigenschaftWert > 0 && $WKEigenschaft->kEigenschaftWert != $oEigenschaftwerte->kEigenschaftWert)
                            ) {
                                $neuePos = true;
                                break;
                            }
                        }
                    }
                }
                if (!$neuePos && !$cUnique) {
                    //erhoehe Anzahl dieser Position
                    $this->PositionenArr[$i]->nZeitLetzteAenderung = time();
                    $this->PositionenArr[$i]->nAnzahl += $anzahl;
                    if ($setzePositionsPreise === true) {
                        $this->setzePositionsPreise();
                    }
                    executeHook(HOOK_WARENKORB_CLASS_FUEGEEIN, array(
                            'kArtikel'      => $kArtikel,
                            'oPosition_arr' => &$this->PositionenArr,
                            'nAnzahl'       => &$anzahl,
                            'exists'        => true
                        )
                    );

                    return $this;
                }
            }
        }

        $NeuePosition = new WarenkorbPos();
        //kopiere Artikel in Warenkorbpos
        $NeuePosition->Artikel               = new Artikel();
        $oArtikelOptionen                    = new stdClass();
        $oArtikelOptionen->nMerkmale         = 1;
        $oArtikelOptionen->nAttribute        = 1;
        $oArtikelOptionen->nArtikelAttribute = 1;
        $oArtikelOptionen->nDownload         = 1;
        $oArtikelOptionen->nKonfig           = 1;

        if ($kKonfigitem > 0) {
            $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
        }
        $NeuePosition->Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);
        $NeuePosition->nAnzahl           = $anzahl;
        $NeuePosition->kArtikel          = $NeuePosition->Artikel->kArtikel;
        $NeuePosition->kVersandklasse    = $NeuePosition->Artikel->kVersandklasse;
        $NeuePosition->kSteuerklasse     = $NeuePosition->Artikel->kSteuerklasse;
        $NeuePosition->fPreisEinzelNetto = $NeuePosition->Artikel->gibPreis($NeuePosition->nAnzahl, array()); // ????
        // $NeuePosition->Artikel->gibPreis(1, isset($Eigenschaft_arr) ? $Eigenschaft_arr : null); <--
        $NeuePosition->fPreis      = $NeuePosition->Artikel->gibPreis($anzahl, isset($Eigenschaft_arr) ? $Eigenschaft_arr : null);
        $NeuePosition->cArtNr      = $NeuePosition->Artikel->cArtNr;
        $NeuePosition->nPosTyp     = $nPosTyp;
        $NeuePosition->cEinheit    = $NeuePosition->Artikel->cEinheit;
        $NeuePosition->cUnique     = $cUnique;
        $NeuePosition->kKonfigitem = $kKonfigitem;

        $NeuePosition->setzeGesamtpreisLoacalized();
        //posname lokalisiert ablegen
        $cLieferstatus_StdSprache    = $NeuePosition->Artikel->cLieferstatus;
        $NeuePosition->cName         = array();
        $NeuePosition->cLieferstatus = array();

        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $NeuePosition->cName[$Sprache->cISO]         = $NeuePosition->Artikel->cName;
            $NeuePosition->cLieferstatus[$Sprache->cISO] = $cLieferstatus_StdSprache;
            if ($Sprache->cStandard !== 'Y') {
                $artikel_spr = Shop::DB()->query("
                  SELECT cName 
                    FROM tartikelsprache 
                    WHERE kArtikel = " . (int) $NeuePosition->kArtikel . " AND kSprache = " . (int) $Sprache->kSprache, 1);
                //Wenn fuer die gewaehlte Sprache kein Name vorhanden ist dann StdSprache nehmen
                $NeuePosition->cName[$Sprache->cISO] = (isset($artikel_spr->cName) && strlen(trim($artikel_spr->cName)) > 0) ?
                    $artikel_spr->cName :
                    $NeuePosition->Artikel->cName;
                $lieferstatus_spr = Shop::DB()->query(
                    "SELECT cName FROM tlieferstatus WHERE kLieferstatus = " .
                    ((isset($NeuePosition->Artikel->kLieferstatus)) ? (int) $NeuePosition->Artikel->kLieferstatus : '') . " AND kSprache = " . (int) $Sprache->kSprache, 1
                );
                if (isset($lieferstatus_spr->cName) && $lieferstatus_spr->cName) {
                    $NeuePosition->cLieferstatus[$Sprache->cISO] = $lieferstatus_spr->cName;
                }
            }
        }
        // Grundpreise bei Staffelpreisen
        if (isset($NeuePosition->Artikel->fVPEWert) && $NeuePosition->Artikel->fVPEWert > 0) {
            $nLast = 0;
            for ($j = 1; $j <= 5; $j++) {
                $cStaffel = 'nAnzahl' . $j;
                if (isset($NeuePosition->Artikel->Preise->$cStaffel) && $NeuePosition->Artikel->Preise->$cStaffel > 0) {
                    if ($NeuePosition->Artikel->Preise->$cStaffel <= $NeuePosition->nAnzahl) {
                        $nLast = $j;
                    }
                }
            }
            if ($nLast > 0) {
                $cStaffel = 'fPreis' . $nLast;
                $NeuePosition->Artikel->baueVPE($NeuePosition->Artikel->Preise->$cStaffel);
            } else {
                $NeuePosition->Artikel->baueVPE();
            }
        }
        $this->setzeKonfig($NeuePosition, false, true);
        if (is_array($NeuePosition->Artikel->Variationen) && count($NeuePosition->Artikel->Variationen) > 0) {
            //foreach ($ewerte as $eWert)
            foreach ($NeuePosition->Artikel->Variationen as $eWert) {
                foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                    //gleiche Eigenschaft suchen
                    if ($oEigenschaftwerte->kEigenschaft == $eWert->kEigenschaft) {
                        if ($eWert->cTyp === 'FREIFELD' || $eWert->cTyp === 'PFLICHT-FREIFELD') {
                            $NeuePosition->setzeVariationsWert($eWert->kEigenschaft, 0, $oEigenschaftwerte->cFreifeldWert);
                        } elseif ($oEigenschaftwerte->kEigenschaftWert > 0) {
                            $EigenschaftWert = new EigenschaftWert($oEigenschaftwerte->kEigenschaftWert);
                            $Eigenschaft     = new Eigenschaft($EigenschaftWert->kEigenschaft);
                            // Varkombi Kind?
                            if ($NeuePosition->Artikel->kVaterArtikel > 0) {
                                if ($Eigenschaft->kArtikel == $NeuePosition->Artikel->kVaterArtikel) {
                                    $NeuePosition->setzeVariationsWert($EigenschaftWert->kEigenschaft, $EigenschaftWert->kEigenschaftWert);
                                }
                            } else {
                                if ($Eigenschaft->kArtikel == $NeuePosition->kArtikel) {
                                    // Variationswert hat eigene Artikelnummer und der Artikel hat nur eine Dimension als Variation?
                                    if (count($NeuePosition->Artikel->Variationen) === 1 && isset($EigenschaftWert->cArtNr) && strlen($EigenschaftWert->cArtNr) > 0) {
                                        $NeuePosition->cArtNr          = $EigenschaftWert->cArtNr;
                                        $NeuePosition->Artikel->cArtNr = $EigenschaftWert->cArtNr;
                                    }

                                    $NeuePosition->setzeVariationsWert($EigenschaftWert->kEigenschaft, $EigenschaftWert->kEigenschaftWert);
                                }
                            }
                        }
                    }
                }
            }
        }

        $NeuePosition->fGesamtgewicht       = $NeuePosition->gibGesamtgewicht();
        $NeuePosition->nZeitLetzteAenderung = time();

        switch ($NeuePosition->nPosTyp) {
            // ArtikelTyp => Gratis Geschenk => Preis nullen
            case C_WARENKORBPOS_TYP_GRATISGESCHENK:
                $NeuePosition->fPreisEinzelNetto = 0;
                $NeuePosition->fPreis            = 0;
                $NeuePosition->setzeGesamtpreisLoacalized();
                break;

            //Pruefen ob eine Versandart hinzugefuegt wird und haenge den Hinweistext an die Position (falls vorhanden)
            case C_WARENKORBPOS_TYP_VERSANDPOS:
                if (isset($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']]) && strlen($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']]) > 0) {
                    $NeuePosition->cHinweis = $_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']];
                }
                break;

            //Pruefen ob eine Zahlungsart hinzugefuegt wird und haenge den Hinweistext an die Position (falls vorhanden)
            case C_WARENKORBPOS_TYP_ZAHLUNGSART:
                if (isset($_SESSION['Zahlungsart']->cHinweisText)) {
                    $NeuePosition->cHinweis = $_SESSION['Zahlungsart']->cHinweisText;
                }
                break;
        }
        unset($NeuePosition->Artikel->oKonfig_arr); //#7482
        $this->PositionenArr[] = $NeuePosition;
        if ($setzePositionsPreise === true) {
            $this->setzePositionsPreise();
        }
        $this->sortShippingPosition();

        executeHook(HOOK_WARENKORB_CLASS_FUEGEEIN, array(
            'kArtikel'      => $kArtikel,
            'oPosition_arr' => &$this->PositionenArr,
            'nAnzahl'       => &$anzahl,
            'exists'        => false
        ));

        return $this;
    }

    /**
     * @return $this
     */
    public function sortShippingPosition()
    {
        if (is_array($this->PositionenArr) && count($this->PositionenArr) > 1) {
            $oPositionVersand = null;
            $i                = 0;
            foreach ($this->PositionenArr as $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_VERSANDPOS) {
                    $oPositionVersand = $oPosition;
                    break;
                }
                $i++;
            }

            if ($oPositionVersand !== null) {
                unset($this->PositionenArr[$i]);
                $this->PositionenArr   = array_merge($this->PositionenArr);
                $this->PositionenArr[] = $oPositionVersand;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function gibLetzteWarenkorbPostionindex()
    {
        return (is_array($this->PositionenArr)) ? (count($this->PositionenArr) - 1) : 0;
    }

    /**
     * @param int $typ
     * @return bool
     */
    public function enthaltenSpezialPos($typ)
    {
        foreach ($this->PositionenArr as $Position) {
            if ($Position->nPosTyp == $typ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $typ
     * @return $this
     */
    public function loescheSpezialPos($typ)
    {
        //loesche evtl. pos dieses typs
        if (isset($this->PositionenArr) && is_array($this->PositionenArr) && count($this->PositionenArr) > 0) {
            $cnt = count($this->PositionenArr);
            for ($i = 0; $i < $cnt; $i++) {
                if (isset($this->PositionenArr[$i]->nPosTyp) && $this->PositionenArr[$i]->nPosTyp == $typ) {
                    unset($this->PositionenArr[$i]);
                }
            }
            $this->PositionenArr = array_merge($this->PositionenArr);
        }

        return $this;
    }

    /**
     * erstellt eine Spezialposition im Warenkorb
     *
     * @param string $name Positionsname
     * @param string $anzahl Positionsanzahl
     * @param string $preis Positionspreis
     * @param string $kSteuerklasse Positionsmwst
     * @param string $typ Positionstyp
     * @param bool   $delSamePosType
     * @param bool   $brutto
     * @param string $hinweis
     * @param bool   $cUnique
     * @param int    $kKonfigitem
     * @param int    $kArtikel
     * @return $this
     */
    public function erstelleSpezialPos($name, $anzahl, $preis, $kSteuerklasse, $typ, $delSamePosType = true, $brutto = true, $hinweis = '', $cUnique = false, $kKonfigitem = 0, $kArtikel = 0)
    {
        $kArtikel = intval($kArtikel);
        if ($delSamePosType) {
            $this->loescheSpezialPos($typ);
        }
        $NeuePosition                = new WarenkorbPos();
        $NeuePosition->nAnzahl       = $anzahl;
        $NeuePosition->nAnzahlEinzel = $anzahl;
        $NeuePosition->kArtikel      = 0;
        $NeuePosition->kSteuerklasse = $kSteuerklasse;
        $NeuePosition->fPreis        = $preis;
        $NeuePosition->cUnique       = $cUnique;
        $NeuePosition->kKonfigitem   = $kKonfigitem;
        $NeuePosition->kArtikel      = $kArtikel;
        //fixes #4967
        if (is_object($_SESSION['Kundengruppe']) && $_SESSION['Kundengruppe']->nNettoPreise > 0) {
            if ($brutto) {
                $NeuePosition->fPreis = $preis / (100 + gibUst($kSteuerklasse)) * 100.0;
            }
            //round net price
            $NeuePosition->fPreis = round($NeuePosition->fPreis, 2);
        } else {
            if ($brutto) {
                //calculate net price based on rounded gross price
                $NeuePosition->fPreis = round($preis, 2) / (100 + gibUst($kSteuerklasse)) * 100.0;
            } else {
                //calculate rounded gross price then calculate net price again.
                $NeuePosition->fPreis = round($preis * (100 + gibUst($kSteuerklasse)) / 100, 2) / (100 + gibUst($kSteuerklasse)) * 100.0;
            }
        }

        $NeuePosition->fPreisEinzelNetto = $NeuePosition->fPreis;
        if (is_array($name)) {
            $NeuePosition->cName = $name;
        } else {
            $NeuePosition->cName = array($_SESSION['cISOSprache'] => $name);
        }
        $NeuePosition->nPosTyp  = $typ;
        $NeuePosition->cHinweis = $hinweis;
        $nOffset                = array_push($this->PositionenArr, $NeuePosition);
        $NeuePosition           = $this->PositionenArr[$nOffset - 1];

        foreach ($_SESSION['Waehrungen'] as $Waehrung) {
            // Standardartikel
            $NeuePosition->cGesamtpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized(berechneBrutto($NeuePosition->fPreis * $NeuePosition->nAnzahl, gibUst($NeuePosition->kSteuerklasse)), $Waehrung);
            $NeuePosition->cGesamtpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($NeuePosition->fPreis * $NeuePosition->nAnzahl, $Waehrung);
            $NeuePosition->cEinzelpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized(berechneBrutto($NeuePosition->fPreis, gibUst($NeuePosition->kSteuerklasse)), $Waehrung);
            $NeuePosition->cEinzelpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($NeuePosition->fPreis, $Waehrung);

            // Konfigurationsartikel: mapto: 9a87wdgad
            if (is_string($NeuePosition->cUnique) && strlen($NeuePosition->cUnique) === 10 && intval($NeuePosition->kKonfigitem) > 0) {
                $fPreisNetto  = 0;
                $fPreisBrutto = 0;
                $nVaterPos    = null;

                foreach ($this->PositionenArr as $nPos => $oPosition) {
                    if ($NeuePosition->cUnique == $oPosition->cUnique) {
                        $fPreisNetto += $oPosition->fPreis * $oPosition->nAnzahl;
                        $fPreisBrutto += berechneBrutto($oPosition->fPreis * $oPosition->nAnzahl, gibUst($oPosition->kSteuerklasse));

                        if (is_string($oPosition->cUnique) && strlen($oPosition->cUnique) === 10 && intval($oPosition->kKonfigitem) === 0) {
                            $nVaterPos = $nPos;
                        }
                    }
                }

                if ($nVaterPos !== null) {
                    $oVaterPos = $this->PositionenArr[$nVaterPos];
                    if (is_object($oVaterPos)) {
                        $NeuePosition->nAnzahlEinzel                           = $NeuePosition->nAnzahl / $oVaterPos->nAnzahl;
                        $oVaterPos->cKonfigpreisLocalized[0][$Waehrung->cName] = gibPreisStringLocalized($fPreisBrutto, $Waehrung);
                        $oVaterPos->cKonfigpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($fPreisNetto, $Waehrung);
                    }
                }
            }
        }
        $this->sortShippingPosition();

        return $this;
    }

    /**
     * stellt fest, ob der Warenkorb alle Eingaben erhalten hat, um den Bestellvorgang durchzufuehren
     *
     * @return int
     * 10 - alles OK, Bestellung kann gemacht werden.
     * 1 - VersandArt fehlt.
     * 2 - Mindestens eine Variation eines Artikels wurde nicht ausgewaehlt
     * 3 - Warenkorb enthaelt keine Positionen
     */
    public function istBestellungMoeglich()
    {
        if (count($this->PositionenArr) < 1) {
            return 3;
        }
        if (isset($_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]) &&
            $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT] > 0 &&
            $this->gibGesamtsummeWaren(1, 0) < $_SESSION['Kundengruppe']->Attribute[KNDGRP_ATTRIBUT_MINDESTBESTELLWERT]
        ) {
            return 9;
        }
        $conf = Shop::getSettings(array(CONF_KAUFABWICKLUNG));
        if ((!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true) && $conf['kaufabwicklung']['bestellabschluss_spamschutz_nutzen'] === 'Y' && $conf['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y') {
            $ip = gibIP(true);
            if ($ip) {
                $cnt = Shop::DB()->query("SELECT count(*) AS anz FROM tbestellung WHERE cIP = '" . Shop::DB()->escape($ip) . "' AND dErstellt>now()-INTERVAL 1 DAY", 1);
                if ($cnt->anz > 0) {
                    $min                = pow(2, $cnt->anz);
                    $bestellungMoeglich = Shop::DB()->query("
                        SELECT dErstellt+interval $min minute < now() AS moeglich
                            FROM tbestellung
                            WHERE cIP='" . Shop::DB()->escape($ip) . "'
                                AND dErstellt>now()-interval 1 day
                                ORDER BY kBestellung DESC", 1
                    );
                    if (!$bestellungMoeglich->moeglich) {
                        return 8;
                    }
                }
            }
        }

        return 10;
    }

    /**
     * gibt Gesamtanzahl Artikel des Warenkorbs zurueck
     *
     * @param array $postyp_arr
     * @return int Anzahl Artikel im Warenkorb
     */
    public function gibAnzahlArtikelExt($postyp_arr)
    {
        if (!is_array($postyp_arr)) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            if (in_array($Position->nPosTyp, $postyp_arr) && (($Position->cUnique == false) || (strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0))) {
                $anz += ($Position->Artikel->cTeilbar === 'Y') ? 1 : $Position->nAnzahl;
            }
        }

        return $anz;
    }

    /**
     * gibt Anzahl der Positionen des Warenkorbs zurueck
     *
     * @param array $postyp_arr
     * @return int Anzahl der Positionen im Warenkorb
     */
    public function gibAnzahlPositionenExt($postyp_arr)
    {
        if (!is_array($postyp_arr)) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            if (in_array($Position->nPosTyp, $postyp_arr) && (($Position->cUnique == false) || (strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0))) {
                $anz++;
            }
        }

        return $anz;
    }

    /**
     * @return bool
     */
    public function hatTeilbareArtikel()
    {
        foreach ($this->PositionenArr as $Position) {
            if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL &&
                isset($Position->Artikel->cTeilbar) &&
                $Position->Artikel->cTeilbar === 'Y'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * gibt Gesamtanzahl eines bestimmten Artikels im Warenkorb zurueck
     *
     * @param int $kArtikel
     * @param int $exclude_pos
     * @return int Anzahl eines bestimmten Artikels im Warenkorb
     */
    public function gibAnzahlEinesArtikels($kArtikel, $exclude_pos = -1)
    {
        if (!$kArtikel) {
            return 0;
        }
        $kArtikel = intval($kArtikel);
        $anz      = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->kArtikel == $kArtikel && $exclude_pos !== $i) {
                $anz += $Position->nAnzahl;
            }
        }

        return $anz;
    }

    /**
     * setzt Preise und entfernt. ggf. cHinweise
     * @return $this
     */
    public function setzePositionsPreise2()
    {
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->kArtikel > 0 && $Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                //kommt man in den Staffelpreisbereich?
                $oArtikel = $Position->Artikel;
                $anz      = $this->gibAnzahlEinesArtikels($oArtikel->kArtikel);
                if ($anz > 1) {
                    $this->PositionenArr[$i]->fPreisEinzelNetto = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                    $this->PositionenArr[$i]->fPreis            = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                    $this->PositionenArr[$i]->fGesamtgewicht    = $this->PositionenArr[$i]->gibGesamtgewicht();

                    if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0 && $_SESSION['Kupon']->nGanzenWKRabattieren == '0') {
                        checkeKuponWKPos($this->PositionenArr[$i], $_SESSION['Kupon']);
                    }
                    $this->PositionenArr[$i]->setzeGesamtpreisLoacalized();
                    unset($this->PositionenArr[$i]->cHinweis);
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setzePositionsPreise()
    {
        $oArtikelOptionen = Artikel::getDefaultOptions();
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->kArtikel > 0 && $Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                $_oldPosition = clone $Position;
                $oArtikel     = new Artikel();
                $oArtikel->fuelleArtikel($Position->kArtikel, $oArtikelOptionen);
                // Baue Variationspreise im Warenkorb neu
                if (is_array($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr) && count($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr) > 0) {
                    foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $j => $oWarenkorbPosEigenschaft) {
                        if (is_array($oArtikel->Variationen) && count($oArtikel->Variationen) > 0) {
                            foreach ($oArtikel->Variationen as $oVariation) {
                                if ($oWarenkorbPosEigenschaft->kEigenschaft == $oVariation->kEigenschaft) {
                                    foreach ($oVariation->Werte as $oEigenschaftWert) {
                                        if ($oWarenkorbPosEigenschaft->kEigenschaftWert == $oEigenschaftWert->kEigenschaftWert) {
                                            $this->PositionenArr[$i]->WarenkorbPosEigenschaftArr[$j]->fAufpreis = (isset($oEigenschaftWert->fAufpreisNetto)) ?
                                                $oEigenschaftWert->fAufpreisNetto :
                                                null;
                                            $this->PositionenArr[$i]->WarenkorbPosEigenschaftArr[$j]->cAufpreisLocalized = (isset($oEigenschaftWert->cAufpreisLocalized[1])) ?
                                                $oEigenschaftWert->cAufpreisLocalized[1] :
                                                null;
                                            break;
                                        }
                                    }

                                    break;
                                }
                            }
                        }
                    }
                }
                $anz                                        = $this->gibAnzahlEinesArtikels($oArtikel->kArtikel);
                $this->PositionenArr[$i]->Artikel           = $oArtikel;
                $this->PositionenArr[$i]->fPreisEinzelNetto = $oArtikel->gibPreis($anz, array());
                $this->PositionenArr[$i]->fPreis            = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                $this->PositionenArr[$i]->fGesamtgewicht    = $this->PositionenArr[$i]->gibGesamtgewicht();
                $this->PositionenArr[$i]->setzeGesamtpreisLoacalized();
                //notify about price changes when the price difference is greater then .01
                if ($_oldPosition->cGesamtpreisLocalized !== $this->PositionenArr[$i]->cGesamtpreisLocalized &&
                    $_oldPosition->Artikel->Preise->fVK !== $this->PositionenArr[$i]->Artikel->Preise->fVK) {
                    $updatedPosition                           = new stdClass();
                    $updatedPosition->cKonfigpreisLocalized    = $this->PositionenArr[$i]->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalized    = $this->PositionenArr[$i]->cGesamtpreisLocalized;
                    $updatedPosition->cName                    = $this->PositionenArr[$i]->cName;
                    $updatedPosition->cKonfigpreisLocalizedOld = $_oldPosition->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalizedOld = $_oldPosition->cGesamtpreisLocalized;
                    $updatedPosition->istKonfigVater           = $this->PositionenArr[$i]->istKonfigVater();
                    self::addUpdatedPosition($updatedPosition);
                }
                unset($this->PositionenArr[$i]->cHinweis);
                if (isset($_SESSION['Kupon']->kKupon) && $_SESSION['Kupon']->kKupon > 0 && intval($_SESSION['Kupon']->nGanzenWKRabattieren) === 0) {
                    $this->PositionenArr[$i] = checkeKuponWKPos($this->PositionenArr[$i], $_SESSION['Kupon']);
                    $this->PositionenArr[$i]->setzeGesamtpreisLoacalized();
                }
            }

            $this->setzeKonfig($this->PositionenArr[$i], true, false);
        }

        return $this;
    }

    /**
     * @param object $oPosition
     * @param bool   $bPreise
     * @param bool   $bName
     * @return $this
     */
    public function setzeKonfig(&$oPosition, $bPreise = true, $bName = true)
    {
        // Falls Konfigitem gesetzt Preise + Name ueberschreiben
        if (intval($oPosition->kKonfigitem) > 0 && class_exists('Konfigitem')) {
            $oKonfigitem = new Konfigitem($oPosition->kKonfigitem);
            if ($oKonfigitem->getKonfigitem() > 0) {
                if ($bPreise) {
                    $oPosition->fPreisEinzelNetto = $oKonfigitem->getPreis(true);
                    $oPosition->fPreis            = $oPosition->fPreisEinzelNetto;
                    $oPosition->kSteuerklasse     = $oKonfigitem->getSteuerklasse();
                    $oPosition->setzeGesamtpreisLoacalized();
                }
                if ($bName && $oKonfigitem->getUseOwnName() && class_exists('Konfigitemsprache')) {
                    foreach ($_SESSION['Sprachen'] as $Sprache) {
                        $oKonfigitemsprache               = new Konfigitemsprache($oKonfigitem->getKonfigitem(), $Sprache->kSprache);
                        $oPosition->cName[$Sprache->cISO] = $oKonfigitemsprache->getName();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * gibt Gesamtanzahl einer bestimmten Variation im Warenkorb zurueck
     *
     * @param int $kArtikel
     * @param int $kEigenschaftsWert
     * @param int $exclude_pos
     * @return int Anzahl einer bestimmten Variation im Warenkorb
     */
    public function gibAnzahlEinerVariation($kArtikel, $kEigenschaftsWert, $exclude_pos = -1)
    {
        if (!$kArtikel || !$kEigenschaftsWert) {
            return 0;
        }
        $anz = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->kArtikel == $kArtikel && $exclude_pos != $i && is_array($Position->WarenkorbPosEigenschaftArr)) {
                foreach ($Position->WarenkorbPosEigenschaftArr as $pos) {
                    if ($pos->kEigenschaftWert == $kEigenschaftsWert) {
                        $anz += $Position->nAnzahl;
                    }
                }
            }
        }

        return $anz;
    }

    /**
     * gibt die tatsaechlichen Versandkosten zurueck, falls eine VersandArt gesetzt ist.
     * Es wird ebenso ueberprueft, ob die Summe fuer versandkostnfrei erreicht wurde.
     * @todo: param?
     * @param string $Lieferland_ISO
     * @return int
     */
    public function gibVersandkostenSteuerklasse($Lieferland_ISO = '')
    {
        $kSteuerklasse = 0;
        $conf          = Shop::getSettings(array(CONF_KAUFABWICKLUNG));
        if ($conf['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
            $nSteuersatz_arr = array();
            foreach ($this->PositionenArr as $i => $Position) {
                if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && $Position->kSteuerklasse > 0) {
                    $nSteuersatz_arr[$Position->kSteuerklasse] += $Position->fPreisEinzelNetto * $Position->nAnzahl;
                }
            }
            $fMaxValue = max($nSteuersatz_arr);
            foreach ($nSteuersatz_arr as $i => $nSteuersatz) {
                if ($nSteuersatz == $fMaxValue) {
                    $kSteuerklasse = $i;
                    break;
                }
            }
        } else {
            $steuersatz = -1;
            foreach ($this->PositionenArr as $i => $Position) {
                if ($Position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && $Position->kSteuerklasse > 0) {
                    if (gibUst($Position->kSteuerklasse) > $steuersatz) {
                        $steuersatz    = gibUst($Position->kSteuerklasse);
                        $kSteuerklasse = $Position->kSteuerklasse;
                    }
                }
            }
        }

        return $kSteuerklasse;
    }

    /**
     * gibt die Versandkosten als String zurueck
     *
     * @return string Text der Versandkosten (lokalisiert)
     */
    public function gibVersandKostenText()
    {
        return (isset($_SESSION['Versandart'])) ?
            Shop::Lang()->get('noShippingCosts', 'basket') :
            (Shop::Lang()->get('plus', 'basket') . ' ' . Shop::Lang()->get('shipping', 'basket'));
    }

    /**
     * Gibt gesamte Warenkorbsumme zurueck.
     *
     * @param bool $Brutto
     * @param bool $gutscheinBeruecksichtigen
     * @return float
     */
    public function gibGesamtsummeWaren($Brutto = false, $gutscheinBeruecksichtigen = true)
    {
        $waehrung = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = $this->Waehrung;
        }
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
        }
        $gesamtsumme = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            // Lokalisierte Preise addieren
            if ($Brutto) {
                $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl * ((100 + gibUst($Position->kSteuerklasse)) / 100);
            } else {
                $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl;
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }
        if ((isset($gutscheinBeruecksichtigen) && $gutscheinBeruecksichtigen) &&
            (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1) &&
            (isset($_SESSION['Bestellung']->fGuthabenGenutzt) && $_SESSION['Bestellung']->fGuthabenGenutzt > 0)
        ) {
            $gesamtsumme -= $_SESSION['Bestellung']->fGuthabenGenutzt * $waehrung->fFaktor;
        }
        // Lokalisierung aufheben
        $gesamtsumme /= $waehrung->fFaktor;
        $this->useSummationRounding();

        return $this->optionaleRundung($gesamtsumme);
    }

    /**
     * Gibt gesamte Warenkorbsumme eines positionstyps zurueck.
     *
     * @param array $postyp_arr
     * @param bool  $Brutto
     * @return float|int
     */
    public function gibGesamtsummeWarenExt($postyp_arr, $Brutto = false)
    {
        if (!is_array($postyp_arr)) {
            return 0;
        }
        $gesamtsumme = 0;
        $waehrung    = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = $this->Waehrung;
        }
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
        }
        foreach ($this->PositionenArr as $i => $Position) {
            if (in_array($Position->nPosTyp, $postyp_arr)) {
                if ($Brutto) {
                    $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl * ((100 + gibUst($Position->kSteuerklasse)) / 100);
                } else {
                    $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl;
                }
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }
        // Lokalisierung aufheben
        $gesamtsumme /= $waehrung->fFaktor;
        $this->useSummationRounding();

        return $this->optionaleRundung($gesamtsumme);
    }

    /**
     * Gibt gesamte Warenkorbsumme ohne bestimmte Positionstypen zurueck.
     *
     * @param array $postyp_arr
     * @param bool  $Brutto
     * @return float|int
     */
    public function gibGesamtsummeWarenOhne($postyp_arr, $Brutto = false)
    {
        if (!is_array($postyp_arr)) {
            return 0;
        }
        $gesamtsumme = 0;
        $waehrung    = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = $this->Waehrung;
        }
        if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
            $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
        }
        foreach ($this->PositionenArr as $i => $Position) {
            if (!in_array($Position->nPosTyp, $postyp_arr)) {
                if ($Brutto) {
                    $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl * ((100 + gibUst($Position->kSteuerklasse)) / 100);
                } else {
                    $gesamtsumme += $Position->fPreis * $waehrung->fFaktor * $Position->nAnzahl;
                }
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }
        // Lokalisierung aufheben
        $gesamtsumme /= $waehrung->fFaktor;
        //$this->useSummationRounding(); // auskommentiert, da momentan nur für Billpay verwendet
        return $gesamtsumme; // optionale Rundung entfernt, da Rappenrundung nicht auf den Betrag bei Billpay angewendet wird.
    }

    /**
     * @param float|int $gesamtsumme
     * @return float
     */
    public function optionaleRundung($gesamtsumme)
    {
        $conf = Shop::getSettings(array(CONF_KAUFABWICKLUNG));
        if (isset($conf['kaufabwicklung']['bestellabschluss_runden5']) && $conf['kaufabwicklung']['bestellabschluss_runden5'] == 1) {
            $waehrung = (isset($_SESSION['Waehrung'])) ? $_SESSION['Waehrung'] : null;
            if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
                $waehrung = $this->Waehrung;
            }
            if (is_null($waehrung) || !isset($waehrung->kWaehrung)) {
                $waehrung = Shop::DB()->query("SELECT * FROM twaehrung WHERE cStandard='Y'", 1);
            }
            $faktor = $waehrung->fFaktor;
            $gesamtsumme *= $faktor;

            // simplification. see https://de.wikipedia.org/wiki/Rundung#Rappenrundung
            $gesamtsumme = round($gesamtsumme * 20) / 20;
            $gesamtsumme /= $faktor;
        }

        return $gesamtsumme;
    }

    /**
     * @return $this
     */
    public function berechnePositionenUst()
    {
        foreach ($this->PositionenArr as $i => $Position) {
            $Position->setzeGesamtpreisLoacalized();
        }

        return $this;
    }

    /**
     * Gibt gesamte Warenkorbsumme lokalisiert als array zurueck.
     *
     * @return float - Gesamtsumme des Warenkorb
     */
    public function gibGesamtsummeWarenLocalized()
    {
        $WarensummeLocalized    = array();
        $WarensummeLocalized[0] = gibPreisStringLocalized($this->gibGesamtsummeWaren(true));
        $WarensummeLocalized[1] = gibPreisStringLocalized($this->gibGesamtsummeWaren());

        return $WarensummeLocalized;
    }

    /**
     * Entfernt Positionen mit nAnzahl 0 im Warenkorb
     * @return $this
     */
    public function loescheNullPositionen()
    {
        foreach ($this->PositionenArr as $i => $Position) {
            if (!($Position->nAnzahl > 0)) {
                unset($this->PositionenArr[$i]);
            }
        }
        $this->PositionenArr = array_merge($this->PositionenArr);

        return $this;
    }

    /**
     * schaut, ob eine Position dieses Typs enthalten ist
     *
     * @param int|string $postyp
     * @return bool
     */
    public function posTypEnthalten($postyp)
    {
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->nPosTyp == $postyp) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function gibSteuerpositionen()
    {
        $steuersatz = array();
        $steuerpos  = array();
        foreach ($this->PositionenArr as $position) {
            if ($position->kSteuerklasse > 0) {
                $ust = gibUst($position->kSteuerklasse);
                if (!in_array($ust, $steuersatz)) {
                    $steuersatz[] = $ust;
                }
            }
        }
        sort($steuersatz);
        foreach ($this->PositionenArr as $position) {
            if ($position->kSteuerklasse > 0) {
                $ust = gibUst($position->kSteuerklasse);
                if ($ust > 0) {
                    $idx = array_search($ust, $steuersatz);
                    if (!isset($steuerpos[$idx]->fBetrag)) {
                        $steuerpos[$idx]                  = new stdClass();
                        $steuerpos[$idx]->cName           = lang_steuerposition($ust, $_SESSION['Kundengruppe']->nNettoPreise);
                        $steuerpos[$idx]->fUst            = $ust;
                        $steuerpos[$idx]->fBetrag         = ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                        $steuerpos[$idx]->cPreisLocalized = gibPreisStringLocalized($steuerpos[$idx]->fBetrag);
                    } else {
                        $steuerpos[$idx]->fBetrag += ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                        $steuerpos[$idx]->cPreisLocalized = gibPreisStringLocalized($steuerpos[$idx]->fBetrag);
                    }
                }
            }
        }

        return $steuerpos;
    }

    /**
     * @return $this
     */
    public function setzeVersandfreiKupon()
    {
        if (is_array($this->PositionenArr) && count($this->PositionenArr) > 0) {
            foreach ($this->PositionenArr as $i => $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_VERSANDPOS) {
                    $this->PositionenArr[$i]->fPreisEinzelNetto = 0.0;
                    $this->PositionenArr[$i]->fPreis            = 0.0;
                    $this->PositionenArr[$i]->setzeGesamtpreisLoacalized();
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * geht alle Positionen durch, korrigiert Lagerbestaende und entfernt Positionen, die nicht mehr vorraetig sind
     *
     * @return $this
     */
    public function pruefeLagerbestaende()
    {
        $bRedirect     = false;
        $positionCount = count($this->PositionenArr);
        for ($i = 0; $i < $positionCount; $i++) {
            if (isset($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr) && count($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr) > 0 &&
                !$this->PositionenArr[$i]->Artikel->kVaterArtikel && !$this->PositionenArr[$i]->Artikel->nIstVater
            ) {
                //Position mit Variationen
                if ($this->PositionenArr[$i]->Artikel->cLagerBeachten === 'Y' && $this->PositionenArr[$i]->Artikel->cLagerVariation === 'Y' &&
                    $this->PositionenArr[$i]->Artikel->cLagerKleinerNull !== 'Y'
                ) {
                    //Lagerbestand in Variationen wird beachtet
                    foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                        if ($oWarenkorbPosEigenschaft->kEigenschaftWert > 0 && $this->PositionenArr[$i]->nAnzahl > 0) {
                            //schaue in DB, ob Lagerbestand ausreichend
                            $oEigenschaftLagerbestand = Shop::DB()->query(
                                "SELECT kEigenschaftWert, fLagerbestand >= " . $this->PositionenArr[$i]->nAnzahl . " AS bAusreichend, fLagerbestand
                                    FROM teigenschaftwert
                                    WHERE kEigenschaftWert = " . (int) $oWarenkorbPosEigenschaft->kEigenschaftWert, 1
                            );

                            if ($oEigenschaftLagerbestand->kEigenschaftWert > 0 && !$oEigenschaftLagerbestand->bAusreichend) {
                                if ($oEigenschaftLagerbestand->fLagerbestand > 0) {
                                    $this->PositionenArr[$i]->nAnzahl = $oEigenschaftLagerbestand->fLagerbestand;
                                } else {
                                    unset($this->PositionenArr[$i]);
                                }
                                $bRedirect = true;
                            }
                        }
                    }
                }
            } else {
                //Position ohne Variationen
                if ($this->PositionenArr[$i]->kArtikel > 0 && $this->PositionenArr[$i]->Artikel->cLagerBeachten === 'Y' &&
                    $this->PositionenArr[$i]->Artikel->cLagerKleinerNull !== 'Y'
                ) {
                    //schaue in DB, ob Lagerbestand ausreichend
                    $oArtikelLagerbestand = Shop::DB()->query(
                        "SELECT kArtikel, fLagerbestand >= " . $this->PositionenArr[$i]->nAnzahl . " AS bAusreichend, fLagerbestand
                            FROM tartikel
                            WHERE kArtikel = " . (int) $this->PositionenArr[$i]->kArtikel, 1
                    );
                    if ($oArtikelLagerbestand->kArtikel > 0 && !$oArtikelLagerbestand->bAusreichend) {
                        if ($oArtikelLagerbestand->fLagerbestand > 0) {
                            $this->PositionenArr[$i]->nAnzahl = $oArtikelLagerbestand->fLagerbestand;
                        } else {
                            unset($this->PositionenArr[$i]);
                        }
                        $bRedirect = true;
                    }
                }
            }
        }
        if ($bRedirect) {
            $this->setzePositionsPreise();
            header('Location: ' . Shop::getURL() . '/warenkorb.php?fillOut=10', true, 303);
            exit;
        }

        return $this;
    }

    /**
     * Setzt Warenkorb mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @access public
     * @param int $kWarenkorb Primary Key
     * @return $this
     */
    public function loadFromDB($kWarenkorb)
    {
        $obj     = Shop::DB()->select('twarenkorb', 'kWarenkorb', intval($kWarenkorb));
        $members = array_keys(get_object_vars($obj));
        foreach ($members as $member) {
            $this->$member = $obj->$member;
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @access public
     * @return int Key vom eingefuegten Warenkorb
     */
    public function insertInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->kWarenkorb);
        unset($obj->PositionenArr);
        unset($obj->cEstimatedDelivery);
        unset($obj->VersandArt);
        if (!isset($obj->kZahlungsInfo) || $obj->kZahlungsInfo === '' || $obj->kZahlungsInfo === null) {
            $obj->kZahlungsInfo = 0;
        }
        $this->kWarenkorb = Shop::DB()->insert('twarenkorb', $obj);

        return $this->kWarenkorb;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     * @access public
     */
    public function updateInDB()
    {
        $obj = kopiereMembers($this);
        unset($obj->PositionenArr);
        unset($obj->cEstimatedDelivery);
        unset($obj->VersandArt);

        return Shop::DB()->update('twarenkorb', 'kWarenkorb', $obj->kWarenkorb, $obj);
    }

    /**
     * @return mixed
     */
    public function getEstimatedDeliveryTime()
    {
        if (is_array($this->PositionenArr) && count($this->PositionenArr) > 0) {
            $longestMinDeliveryDays = 0;
            $longestMaxDeliveryDays = 0;

            foreach ($this->PositionenArr as $i => $oPosition) {
                if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL && get_class($oPosition->Artikel) === 'Artikel') {
                    $oPosition->cEstimatedDelivery = $oPosition->Artikel->getDeliveryTime($_SESSION['cLieferlandISO'], $oPosition->nAnzahl);
                    if (isset($oPosition->Artikel->nMinDeliveryDays) && $oPosition->Artikel->nMinDeliveryDays > $longestMinDeliveryDays) {
                        $longestMinDeliveryDays = $oPosition->Artikel->nMinDeliveryDays;
                    }
                    if (isset($oPosition->Artikel->nMaxDeliveryDays) && $oPosition->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays) {
                        $longestMaxDeliveryDays = $oPosition->Artikel->nMaxDeliveryDays;
                    }
                }
            }

            $cEstimatedDelivery = getDeliverytimeEstimationText($longestMinDeliveryDays, $longestMaxDeliveryDays);

            return $cEstimatedDelivery;
        }

        return '';
    }

    /**
     * @return object|null
     */
    public function gibLetztenWKArtikel()
    {
        if (is_array($this->PositionenArr)) {
            $nZeitLetzteAenderung = 0;
            $oResult              = null;
            $positionCount        = count($this->PositionenArr) - 1;
            for ($i = $positionCount; $i >= 0; $i--) {
                if ($this->PositionenArr[$i]->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL
                    && $this->PositionenArr[$i]->kKonfigitem == 0
                ) {
                    if (isset($this->PositionenArr[$i]->nZeitLetzteAenderung) && $this->PositionenArr[$i]->nZeitLetzteAenderung > $nZeitLetzteAenderung
                    ) {
                        $nZeitLetzteAenderung = $this->PositionenArr[$i]->nZeitLetzteAenderung;
                        $oResult              = $this->PositionenArr[$i]->Artikel;
                    } elseif ($oResult === null) { //Wenn keine nZeitLetzteAenderung gesetzt ist letztes Element des WK-Arrays nehmen
                        $oResult = $this->PositionenArr[$i]->Artikel;
                    }
                }
            }
            if ($oResult !== null) {
                return $oResult;
            }
        }

        return;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        $gewicht = 0;
        if (is_array($this->PositionenArr)) {
            foreach ($this->PositionenArr as $pos) {
                $gewicht += $pos->fGesamtgewicht;
            }
        }

        return $gewicht;
    }

    /**
     * @param bool $isRedirect
     * @param bool $unique
     */
    public function redirectTo($isRedirect = false, $unique = false)
    {
        $conf = Shop::getSettings(array(CONF_GLOBAL));
        if (!isset($_SESSION['variBoxAnzahl_arr']) && $conf['global']['global_warenkorb_weiterleitung'] === 'Y' && !$isRedirect && !$unique) {
            header('Location: warenkorb.php', true, 303);
            exit;
        }
    }

    /**
     * Unique hash to identify any basket changes
     * @return string
     */
    public function getUniqueHash()
    {
        return sha1(serialize($this));
    }

    /**
     * make sure the applied coupons are still valid after removing items from the cart
     * or updating amounts
     *
     * @return bool
     */
    public function checkIfCouponIsStillValid()
    {
        $isValid = true;
        if (isset($_SESSION['Kupon']->kKupon)) {
            if ($this->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)) {
                // Kupon darf nicht im leeren Warenkorb eingelöst werden
                if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
                    $Kupon = Shop::DB()->select('tkupon', 'kKupon', intval($_SESSION['Kupon']->kKupon));
                    if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                        $isValid = (1 === angabenKorrekt(checkeKupon($Kupon)));
                    } elseif (!empty($Kupon->kKupon) && $Kupon->cKuponTyp === 'versandkupon') {
                        //@todo?
                    } else {
                        $isValid = false;
                    }
                }
                if ($isValid === false) {
                    unset($_SESSION['Kupon']);
                    $this->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                         ->setzePositionsPreise();
                }
            } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren) && $_SESSION['Kupon']->nGanzenWKRabattieren === '0' &&
                $_SESSION['Kupon']->cKuponTyp === 'standard' && $_SESSION['Kupon']->cWertTyp === 'prozent') {
                if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt(array(C_WARENKORBPOS_TYP_ARTIKEL)) > 0) {
                    $Kupon = Shop::DB()->select('tkupon', 'kKupon', intval($_SESSION['Kupon']->kKupon));
                    if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                        $isValid = (1 === angabenKorrekt(checkeKupon($Kupon)));
                    } else {
                        $isValid = false;
                    }
                }
                if ($isValid === false) {
                    unset($_SESSION['Kupon']);
                    $this->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                        ->setzePositionsPreise();
                }
            } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren) && $_SESSION['Kupon']->nGanzenWKRabattieren === '0' &&
                $_SESSION['Kupon']->cKuponTyp === 'standard') {
                //we have a coupon in the current session but none in the cart.
                //this happens with coupons tied to special articles that are no longer valid.
                unset($_SESSION['Kupon']);
            }
        }

        return $isValid;
    }

    /**
     * use summation rounding to even out discrepancies between total basket sum and sum of basket position totals
     * @param int $precision - decimal precision used (default 2)
     */
    public function useSummationRounding($precision = 2)
    {
        $count             = count($this->PositionenArr);
        $cumulatedDelta    = 0;
        $cumulatedDeltaNet = 0;
        foreach ($_SESSION['Waehrungen'] as $Waehrung) {
            for ($i = 0; $i < $count; $i++) {
                $position    = $this->PositionenArr[$i];
                $grossAmount = berechneBrutto($position->fPreis * $position->nAnzahl, gibUst($position->kSteuerklasse),
                    12);
                $netAmount          = $position->fPreis * $position->nAnzahl;
                $roundedGrossAmount = berechneBrutto($position->fPreis * $position->nAnzahl + $cumulatedDelta,
                    gibUst($position->kSteuerklasse), $precision);
                $roundedNetAmount = round($position->fPreis * $position->nAnzahl + $cumulatedDeltaNet, $precision);

                if ($i != 0 && $position->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                    if ($grossAmount != 0) {
                        $this->PositionenArr[$i]->cGesamtpreisLocalized[0][$Waehrung->cName] =
                            gibPreisStringLocalized($roundedGrossAmount, $Waehrung);
                    }
                    if ($netAmount != 0) {
                        $this->PositionenArr[$i]->cGesamtpreisLocalized[1][$Waehrung->cName] = gibPreisStringLocalized($roundedNetAmount,
                            $Waehrung);
                    }
                }
                $cumulatedDelta += ($grossAmount - $roundedGrossAmount);
                $cumulatedDeltaNet += ($netAmount - $roundedNetAmount);
            }
        }
    }
}
