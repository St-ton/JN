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
     * @var WarenkorbPos[]
     */
    public $PositionenArr = [];

    /**
     * @var string
     */
    public $cEstimatedDelivery = '';

    /**
     * @var string
     */
    public $cChecksumme = '';

    /**
     * @var object
     */
    public $Waehrung;

    /**
     * @var array
     */
    public static $updatedPositions = [];

    /**
     * @var array
     */
    public static $deletedPositions = [];

    /**
     * @var array
     */
    private $config;

    /**
     * Konstruktor
     *
     * @param int $kWarenkorb Falls angegeben, wird der Warenkorb mit angegebenem kWarenkorb aus der DB geholt
     */
    public function __construct($kWarenkorb = 0)
    {
        $this->config = Shop::getSettings([CONF_GLOBAL, CONF_KAUFABWICKLUNG]);
        $kWarenkorb   = (int)$kWarenkorb;
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
        foreach ($this->PositionenArr as $i => $Position) {
            $Position->nPosTyp = (int)$Position->nPosTyp;
            $delete            = false;
            if (!empty($Position->Artikel)) {
                if (isset($Position->Artikel->fLagerbestand,
                        $Position->Artikel->cLagerBeachten,
                        $Position->Artikel->cLagerKleinerNull,
                        $Position->Artikel->cLagerVariation
                    )
                    && $Position->Artikel->fLagerbestand <= 0
                    && $Position->Artikel->cLagerBeachten === 'Y'
                    && $Position->Artikel->cLagerKleinerNull !== 'Y'
                    && $Position->Artikel->cLagerVariation !== 'Y'
                ) {
                    $delete = true;
                } elseif (empty($Position->kKonfigitem)
                    && $Position->fPreisEinzelNetto == 0
                    && !$Position->Artikel->bHasKonfig
                    && $Position->nPosTyp !== C_WARENKORBPOS_TYP_GRATISGESCHENK
                    && isset($Position->fPreisEinzelNetto, $this->config['global']['global_preis0'])
                    && $this->config['global']['global_preis0'] === 'N'
                ) {
                    $delete = true;
                } elseif (!empty($Position->Artikel->FunktionsAttribute[FKT_ATTRIBUT_UNVERKAEUFLICH])) {
                    $delete = true;
                } else {
                    $delete = (Shop::DB()->select('tartikel', 'kArtikel', $Position->kArtikel) === null);
                }

                executeHook(HOOK_WARENKORB_CLASS_LOESCHEDEAKTIVIERTEPOS, [
                    'oPosition' => $Position,
                    'delete'    => &$delete
                ]);
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
        $kArtikel = (int)$kArtikel;
        //toDo schaue, ob diese Pos nicht markiert werden muesste, wenn anzahl>lager gekauft wird
        //schaue, ob es nicht schon Positionen mit diesem Artikel gibt
        foreach ($this->PositionenArr as $i => $Position) {
            if (!(isset($Position->Artikel->kArtikel)
                && $Position->Artikel->kArtikel == $kArtikel
                && $Position->nPosTyp == $nPosTyp
                && !$Position->cUnique)
            ) {
                continue;
            }
            $neuePos = false;
            //hat diese Position schon einen EigenschaftWert ausgewaehlt und ist das dieselbe eigenschaft wie ausgewaehlt?
            foreach ($Position->WarenkorbPosEigenschaftArr as $WKEigenschaft) {
                foreach ($oEigenschaftwerte_arr as $oEigenschaftwerte) {
                    //gleiche Eigenschaft suchen
                    if ($oEigenschaftwerte->kEigenschaft != $WKEigenschaft->kEigenschaft) {
                        continue;
                    }
                    //ist es ein Freifeld mit unterschieldichem Inhalt oder eine Eigenschaft mit unterschielichem Wert?
                    if (($WKEigenschaft->kEigenschaftWert > 0
                            && $WKEigenschaft->kEigenschaftWert != $oEigenschaftwerte->kEigenschaftWert)
                        || (($WKEigenschaft->cTyp === 'FREIFELD' || $WKEigenschaft->cTyp === 'PFLICHT-FREIFELD')
                            && $WKEigenschaft->cEigenschaftWertName[$_SESSION['cISOSprache']] != $oEigenschaftwerte->cFreifeldWert)
                    ) {
                        $neuePos = true;
                        break;
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
                executeHook(HOOK_WARENKORB_CLASS_FUEGEEIN, [
                    'kArtikel'      => $kArtikel,
                    'oPosition_arr' => &$this->PositionenArr,
                    'nAnzahl'       => &$anzahl,
                    'exists'        => true
                ]);

                return $this;
            }
        }

        $NeuePosition = new WarenkorbPos();
        //kopiere Artikel in Warenkorbpos
        $NeuePosition->Artikel = new Artikel();
        $oArtikelOptionen      = Artikel::getDefaultOptions();
        if ($kKonfigitem > 0) {
            $oArtikelOptionen->nKeineSichtbarkeitBeachten = 1;
        }
        $NeuePosition->Artikel->fuelleArtikel($kArtikel, $oArtikelOptionen);
        $NeuePosition->nAnzahl           = $anzahl;
        $NeuePosition->kArtikel          = $NeuePosition->Artikel->kArtikel;
        $NeuePosition->kVersandklasse    = $NeuePosition->Artikel->kVersandklasse;
        $NeuePosition->kSteuerklasse     = $NeuePosition->Artikel->kSteuerklasse;
        $NeuePosition->fPreisEinzelNetto = $NeuePosition->Artikel->gibPreis($NeuePosition->nAnzahl, []);
        $NeuePosition->fPreis      = $NeuePosition->Artikel->gibPreis($anzahl, []);
        $NeuePosition->cArtNr      = $NeuePosition->Artikel->cArtNr;
        $NeuePosition->nPosTyp     = $nPosTyp;
        $NeuePosition->cEinheit    = $NeuePosition->Artikel->cEinheit;
        $NeuePosition->cUnique     = $cUnique;
        $NeuePosition->kKonfigitem = $kKonfigitem;

        $NeuePosition->setzeGesamtpreisLocalized();
        //posname lokalisiert ablegen
        $cLieferstatus_StdSprache    = $NeuePosition->Artikel->cLieferstatus;
        $NeuePosition->cName         = [];
        $NeuePosition->cLieferstatus = [];

        foreach ($_SESSION['Sprachen'] as $Sprache) {
            $NeuePosition->cName[$Sprache->cISO]         = $NeuePosition->Artikel->cName;
            $NeuePosition->cLieferstatus[$Sprache->cISO] = $cLieferstatus_StdSprache;
            if ($Sprache->cStandard === 'Y') {
                continue;
            }
            $artikel_spr = Shop::DB()->select(
                'tartikelsprache',
                'kArtikel',
                (int)$NeuePosition->kArtikel,
                'kSprache',
                (int)$Sprache->kSprache
            );
            //Wenn fuer die gewaehlte Sprache kein Name vorhanden ist dann StdSprache nehmen
            $NeuePosition->cName[$Sprache->cISO] = (isset($artikel_spr->cName) && strlen(trim($artikel_spr->cName)) > 0)
                ? $artikel_spr->cName
                : $NeuePosition->Artikel->cName;
            $lieferstatus_spr = Shop::DB()->select(
                'tlieferstatus',
                'kLieferstatus', (isset($NeuePosition->Artikel->kLieferstatus)
                    ? (int)$NeuePosition->Artikel->kLieferstatus
                    : ''),
                'kSprache',
                (int)$Sprache->kSprache
            );
            if (!empty($lieferstatus_spr->cName)) {
                $NeuePosition->cLieferstatus[$Sprache->cISO] = $lieferstatus_spr->cName;
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
                    if ($oEigenschaftwerte->kEigenschaft != $eWert->kEigenschaft) {
                        continue;
                    }
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
                                if (isset($EigenschaftWert->cArtNr)
                                    && count($NeuePosition->Artikel->Variationen) === 1
                                    && strlen($EigenschaftWert->cArtNr) > 0
                                ) {
                                    $NeuePosition->cArtNr          = $EigenschaftWert->cArtNr;
                                    $NeuePosition->Artikel->cArtNr = $EigenschaftWert->cArtNr;
                                }

                                $NeuePosition->setzeVariationsWert($EigenschaftWert->kEigenschaft, $EigenschaftWert->kEigenschaftWert);

                                // aktuellen Eigenschaftswert mit Bild ermitteln und Variationsbild an der Position speichern
                                $kEigenschaftWert = $EigenschaftWert->kEigenschaftWert;
                                $oVariationWert   = current(array_filter($eWert->Werte, function ($item) use ($kEigenschaftWert) {
                                    return $item->kEigenschaftWert === $kEigenschaftWert && !empty($item->cPfadNormal);
                                }));

                                if ($oVariationWert !== false) {
                                    WarenkorbHelper::setVariationPicture($NeuePosition, $oVariationWert);
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
                $NeuePosition->setzeGesamtpreisLocalized();
                break;

            //Pruefen ob eine Versandart hinzugefuegt wird und haenge den Hinweistext an die Position (falls vorhanden)
            case C_WARENKORBPOS_TYP_VERSANDPOS:
                if (isset($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']])
                    && strlen($_SESSION['Versandart']->angezeigterHinweistext[$_SESSION['cISOSprache']]) > 0
                ) {
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
        $this->updateCouponValue();
        $this->sortShippingPosition();

        executeHook(HOOK_WARENKORB_CLASS_FUEGEEIN, [
            'kArtikel'      => $kArtikel,
            'oPosition_arr' => &$this->PositionenArr,
            'nAnzahl'       => &$anzahl,
            'exists'        => false
        ]);

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
                $oPosition->nPosTyp = (int)$oPosition->nPosTyp;
                if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_VERSANDPOS) {
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
        return is_array($this->PositionenArr) ? (count($this->PositionenArr) - 1) : 0;
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
        $typ = (int)$typ;
        if (count($this->PositionenArr) > 0) {
            $cnt = count($this->PositionenArr);
            for ($i = 0; $i < $cnt; $i++) {
                if (isset($this->PositionenArr[$i]->nPosTyp) && (int)$this->PositionenArr[$i]->nPosTyp === $typ) {
                    unset($this->PositionenArr[$i]);
                }
            }
            $this->PositionenArr = array_merge($this->PositionenArr);
            if (!empty($_POST['Kuponcode']) && $typ === C_WARENKORBPOS_TYP_KUPON) {
                if (!empty($_SESSION['Kupon'])) {
                    unset($_SESSION['Kupon']);
                } elseif (!empty($_SESSION['oVersandfreiKupon'])) {
                    unset($_SESSION['oVersandfreiKupon']);
                    if (!empty($_SESSION['VersandKupon'])) {
                        unset($_SESSION['VersandKupon']);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * erstellt eine Spezialposition im Warenkorb
     *
     * @param string|array $name Positionsname
     * @param string       $anzahl Positionsanzahl
     * @param string       $preis Positionspreis
     * @param string       $kSteuerklasse Positionsmwst
     * @param int          $typ Positionstyp
     * @param bool         $delSamePosType
     * @param bool         $brutto
     * @param string       $hinweis
     * @param bool         $cUnique
     * @param int          $kKonfigitem
     * @param int          $kArtikel
     * @return $this
     */
    public function erstelleSpezialPos(
        $name,
        $anzahl,
        $preis,
        $kSteuerklasse,
        $typ,
        $delSamePosType = true,
        $brutto = true,
        $hinweis = '',
        $cUnique = false,
        $kKonfigitem = 0,
        $kArtikel = 0
    )
    {
        $typ      = (int)$typ;
        $kArtikel = (int)$kArtikel;
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
        if (is_object($_SESSION['Kundengruppe']) && Session::CustomerGroup()->isMerchant()) {
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
                $NeuePosition->fPreis = round($preis * (100 + gibUst($kSteuerklasse)) / 100, 2) /
                    (100 + gibUst($kSteuerklasse)) * 100.0;
            }
        }

        $NeuePosition->fPreisEinzelNetto = $NeuePosition->fPreis;
        if ($typ === C_WARENKORBPOS_TYP_KUPON && isset($name->cName)) {
            $NeuePosition->cName = is_array($name->cName)
                ? $name->cName
                : [$_SESSION['cISOSprache'] => $name->cName];
            if (isset($name->cArticleNameAffix, $name->discountForArticle)) {
                $NeuePosition->cArticleNameAffix  = $name->cArticleNameAffix;
                $NeuePosition->discountForArticle = $name->discountForArticle;
            }
        } else {
            $NeuePosition->cName = is_array($name)
                ? $name
                : [$_SESSION['cISOSprache'] => $name];
        }
        $NeuePosition->nPosTyp  = $typ;
        $NeuePosition->cHinweis = $hinweis;
        $nOffset                = array_push($this->PositionenArr, $NeuePosition);
        $NeuePosition           = $this->PositionenArr[$nOffset - 1];
        foreach (Session::Currencies() as $currency) {
            $currencyName = $currency->getName();
            // Standardartikel
            $NeuePosition->cGesamtpreisLocalized[0][$currencyName] = gibPreisStringLocalized(
                berechneBrutto($NeuePosition->fPreis * $NeuePosition->nAnzahl, gibUst($NeuePosition->kSteuerklasse)),
                $currency
            );
            $NeuePosition->cGesamtpreisLocalized[1][$currencyName] = gibPreisStringLocalized(
                $NeuePosition->fPreis * $NeuePosition->nAnzahl,
                $currency
            );
            $NeuePosition->cEinzelpreisLocalized[0][$currencyName] = gibPreisStringLocalized(
                berechneBrutto($NeuePosition->fPreis, gibUst($NeuePosition->kSteuerklasse)),
                $currency
            );
            $NeuePosition->cEinzelpreisLocalized[1][$currencyName] = gibPreisStringLocalized(
                $NeuePosition->fPreis,
                $currency
            );

            // Konfigurationsartikel: mapto: 9a87wdgad
            if ((int)$NeuePosition->kKonfigitem > 0 &&
                is_string($NeuePosition->cUnique) &&
                strlen($NeuePosition->cUnique) === 10
            ) {
                $fPreisNetto  = 0;
                $fPreisBrutto = 0;
                $nVaterPos    = null;

                foreach ($this->PositionenArr as $nPos => $oPosition) {
                    if ($NeuePosition->cUnique === $oPosition->cUnique) {
                        $fPreisNetto += $oPosition->fPreis * $oPosition->nAnzahl;
                        $fPreisBrutto += berechneBrutto(
                            $oPosition->fPreis * $oPosition->nAnzahl,
                            gibUst($oPosition->kSteuerklasse)
                        );

                        if ((int)$oPosition->kKonfigitem === 0
                            && is_string($oPosition->cUnique)
                            && strlen($oPosition->cUnique) === 10
                        ) {
                            $nVaterPos = $nPos;
                        }
                    }
                }

                if ($nVaterPos !== null) {
                    $oVaterPos = $this->PositionenArr[$nVaterPos];
                    if (is_object($oVaterPos)) {
                        $NeuePosition->nAnzahlEinzel                        = $NeuePosition->nAnzahl / $oVaterPos->nAnzahl;
                        $oVaterPos->cKonfigpreisLocalized[0][$currencyName] = gibPreisStringLocalized($fPreisBrutto, $currency);
                        $oVaterPos->cKonfigpreisLocalized[1][$currencyName] = gibPreisStringLocalized($fPreisNetto, $currency);
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
        $mbw = Session::CustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
        if ($mbw > 0 && $this->gibGesamtsummeWaren(1, 0) < $mbw) {
            return 9;
        }
        if ((!isset($_SESSION['bAnti_spam_already_checked']) || $_SESSION['bAnti_spam_already_checked'] !== true)
            && $this->config['kaufabwicklung']['bestellabschluss_spamschutz_nutzen'] === 'Y'
            && $this->config['kaufabwicklung']['bestellabschluss_ip_speichern'] === 'Y'
            && ($ip = gibIP(true))
        ) {
            $cnt = Shop::DB()->executeQueryPrepared(
                "SELECT count(*) AS anz 
                    FROM tbestellung 
                    WHERE cIP = :ip 
                        AND dErstellt > now()-INTERVAL 1 DAY",
                ['ip' => Shop::DB()->escape($ip)],
                1
            );
            if ($cnt->anz > 0) {
                $min                = pow(2, $cnt->anz);
                $min                = min([$min, 1440]);
                $bestellungMoeglich = Shop::DB()->executeQueryPrepared(
                    "SELECT dErstellt+INTERVAL $min MINUTE < now() AS moeglich
                        FROM tbestellung
                        WHERE cIP = :ip
                            AND dErstellt>now()-INTERVAL 1 day
                        ORDER BY kBestellung DESC",
                    ['ip' => Shop::DB()->escape($ip)],
                    1
                );
                if (!$bestellungMoeglich->moeglich) {
                    return 8;
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
        foreach ($this->PositionenArr as $Position) {
            if (in_array($Position->nPosTyp, $postyp_arr)
                && (($Position->cUnique === false) || (strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0))
            ) {
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
            if (in_array($Position->nPosTyp, $postyp_arr)
                && ($Position->cUnique === false || (strlen($Position->cUnique) > 0 && $Position->kKonfigitem == 0))
            ) {
                ++$anz;
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
            $Position->nPosTyp = (int)$Position->nPosTyp;
            if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                && isset($Position->Artikel->cTeilbar)
                && $Position->Artikel->cTeilbar === 'Y'
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
        $kArtikel = (int)$kArtikel;
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
            $Position->nPosTyp = (int)$Position->nPosTyp;
            if ($Position->kArtikel <= 0 || $Position->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            //kommt man in den Staffelpreisbereich?
            /** @var $oArtikel Artikel */
            $oArtikel = $Position->Artikel;
            $anz      = $this->gibAnzahlEinesArtikels($oArtikel->kArtikel);
            if ($anz > 1) {
                $this->PositionenArr[$i]->fPreisEinzelNetto = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                $this->PositionenArr[$i]->fPreis            = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                $this->PositionenArr[$i]->fGesamtgewicht    = $this->PositionenArr[$i]->gibGesamtgewicht();

                if (isset($_SESSION['Kupon']->kKupon)
                    && $_SESSION['Kupon']->kKupon > 0
                    && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
                ) {
                    checkeKuponWKPos($this->PositionenArr[$i], $_SESSION['Kupon']);
                }
                $this->PositionenArr[$i]->setzeGesamtpreisLocalized();
                unset($this->PositionenArr[$i]->cHinweis);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setzePositionsPreise()
    {
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($this->PositionenArr as $i => $Position) {
            if ($Position->kArtikel > 0 && $Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                $_oldPosition = clone $Position;
                $oArtikel     = new Artikel();
                if (!$oArtikel->fuelleArtikel($Position->kArtikel, $defaultOptions)) {
                    continue;
                }
                // Baue Variationspreise im Warenkorb neu, aber nur wenn es ein gültiger Artikel ist
                if (is_array($Position->WarenkorbPosEigenschaftArr)) {
                    foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $j => $oWarenkorbPosEigenschaft) {
                        if (!is_array($oArtikel->Variationen)) {
                            continue;
                        }
                        foreach ($oArtikel->Variationen as $oVariation) {
                            if ($oWarenkorbPosEigenschaft->kEigenschaft != $oVariation->kEigenschaft) {
                                continue;
                            }
                            foreach ($oVariation->Werte as $oEigenschaftWert) {
                                if ($oWarenkorbPosEigenschaft->kEigenschaftWert == $oEigenschaftWert->kEigenschaftWert) {
                                    $this->PositionenArr[$i]->WarenkorbPosEigenschaftArr[$j]->fAufpreis = isset($oEigenschaftWert->fAufpreisNetto)
                                        ? $oEigenschaftWert->fAufpreisNetto
                                        : null;
                                    $this->PositionenArr[$i]->WarenkorbPosEigenschaftArr[$j]->cAufpreisLocalized = isset($oEigenschaftWert->cAufpreisLocalized[1])
                                        ? $oEigenschaftWert->cAufpreisLocalized[1]
                                        : null;
                                    break;
                                }
                            }

                            break;
                        }
                    }
                }
                $anz = $this->gibAnzahlEinesArtikels($oArtikel->kArtikel);
                $Position->Artikel           = $oArtikel;
                $Position->fPreisEinzelNetto = $oArtikel->gibPreis($anz, []);
                $Position->fPreis            = $oArtikel->gibPreis($anz, $Position->WarenkorbPosEigenschaftArr);
                $Position->fGesamtgewicht    = $Position->gibGesamtgewicht();
                $Position->setzeGesamtpreisLocalized();
                //notify about price changes when the price difference is greater then .01
                if ($_oldPosition->cGesamtpreisLocalized !== $Position->cGesamtpreisLocalized
                    && $_oldPosition->Artikel->Preise->fVK !== $Position->Artikel->Preise->fVK
                ) {
                    $updatedPosition = new stdClass();
                    $updatedPosition->cKonfigpreisLocalized    = $Position->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalized    = $Position->cGesamtpreisLocalized;
                    $updatedPosition->cName                    = $Position->cName;
                    $updatedPosition->cKonfigpreisLocalizedOld = $_oldPosition->cKonfigpreisLocalized;
                    $updatedPosition->cGesamtpreisLocalizedOld = $_oldPosition->cGesamtpreisLocalized;
                    $updatedPosition->istKonfigVater           = $Position->istKonfigVater();
                    self::addUpdatedPosition($updatedPosition);
                }
                unset($Position->cHinweis);
                if (isset($_SESSION['Kupon']->kKupon)
                    && $_SESSION['Kupon']->kKupon > 0
                    && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
                ) {
                    $Position = checkeKuponWKPos($Position, $_SESSION['Kupon']);
                    $Position->setzeGesamtpreisLocalized();
                }
            }

            $this->setzeKonfig($Position, true, false);
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
        if ((int)$oPosition->kKonfigitem > 0 && class_exists('Konfigitem')) {
            $oKonfigitem = new Konfigitem($oPosition->kKonfigitem);
            if ($oKonfigitem->getKonfigitem() > 0) {
                if ($bPreise) {
                    $oPosition->fPreisEinzelNetto = $oKonfigitem->getPreis(true);
                    $oPosition->fPreis            = $oPosition->fPreisEinzelNetto;
                    $oPosition->kSteuerklasse     = $oKonfigitem->getSteuerklasse();
                    $oPosition->setzeGesamtpreisLocalized();
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
            if ($Position->kArtikel == $kArtikel && $exclude_pos != $i
                && is_array($Position->WarenkorbPosEigenschaftArr)
            ) {
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
        if ($this->config['kaufabwicklung']['bestellvorgang_versand_steuersatz'] === 'US') {
            $nSteuersatz_arr = [];
            foreach ($this->PositionenArr as $i => $Position) {
                if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $Position->kSteuerklasse > 0) {
                    if (empty($nSteuersatz_arr[$Position->kSteuerklasse])) {
                        $nSteuersatz_arr[$Position->kSteuerklasse] = $Position->fPreisEinzelNetto * $Position->nAnzahl;
                    } else {
                        $nSteuersatz_arr[$Position->kSteuerklasse] += $Position->fPreisEinzelNetto * $Position->nAnzahl;
                    }
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
                if ($Position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL && $Position->kSteuerklasse > 0) {
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
        return isset($_SESSION['Versandart'])
            ? Shop::Lang()->get('noShippingCosts', 'basket')
            : (Shop::Lang()->get('plus', 'basket') . ' ' . Shop::Lang()->get('shipping', 'basket'));
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
        $currency         = $this->Waehrung === null ? Session::Currency() : $this->Waehrung;
        $conversionFactor = $currency->getConversionFactor();
        $gesamtsumme      = 0;
        foreach ($this->PositionenArr as $i => $Position) {
            // Lokalisierte Preise addieren
            if ($Brutto) {
                $gesamtsumme += $Position->fPreis * $conversionFactor * $Position->nAnzahl *
                    ((100 + gibUst($Position->kSteuerklasse)) / 100);
            } else {
                $gesamtsumme += $Position->fPreis * $conversionFactor * $Position->nAnzahl;
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }
        if (!empty($gutscheinBeruecksichtigen)
            && (isset($_SESSION['Bestellung']->GuthabenNutzen) && $_SESSION['Bestellung']->GuthabenNutzen == 1)
            && (isset($_SESSION['Bestellung']->fGuthabenGenutzt) && $_SESSION['Bestellung']->fGuthabenGenutzt > 0)
        ) {
            $gesamtsumme -= $_SESSION['Bestellung']->fGuthabenGenutzt * $conversionFactor;
        }
        // Lokalisierung aufheben
        $gesamtsumme /= $conversionFactor;
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
        foreach ($this->PositionenArr as $i => $Position) {
            if (in_array($Position->nPosTyp, $postyp_arr, true)) {
                if ($Brutto) {
                    $gesamtsumme += $Position->fPreis * $Position->nAnzahl *
                        ((100 + gibUst($Position->kSteuerklasse)) / 100);
                } else {
                    $gesamtsumme += $Position->fPreis * $Position->nAnzahl;
                }
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }
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
        $currency    = $this->Waehrung === null ? Session::Currency() : $this->Waehrung;
        $factor      = $currency->getConversionFactor();
        foreach ($this->PositionenArr as $Position) {
            if (!in_array($Position->nPosTyp, $postyp_arr)) {
                if ($Brutto) {
                    $gesamtsumme += $Position->fPreis * $factor * $Position->nAnzahl *
                        ((100 + gibUst($Position->kSteuerklasse)) / 100);
                } else {
                    $gesamtsumme += $Position->fPreis * $factor* $Position->nAnzahl;
                }
            }
        }
        if ($Brutto) {
            $gesamtsumme = round($gesamtsumme, 2);
        }

        return $gesamtsumme / $factor;
    }

    /**
     * @param float|int $gesamtsumme
     * @return float
     */
    public function optionaleRundung($gesamtsumme)
    {
        if (isset($this->config['kaufabwicklung']['bestellabschluss_runden5'])
            && (int)$this->config['kaufabwicklung']['bestellabschluss_runden5'] === 1
        ) {
            $currency    = $this->Waehrung === null ? Session::Currency() : $this->Waehrung;
            $factor      = $currency->getConversionFactor();
            $gesamtsumme *= $factor;

            // simplification. see https://de.wikipedia.org/wiki/Rundung#Rappenrundung
            $gesamtsumme = round($gesamtsumme * 20) / 20;
            $gesamtsumme /= $factor;
        }

        return $gesamtsumme;
    }

    /**
     * @return $this
     */
    public function berechnePositionenUst()
    {
        foreach ($this->PositionenArr as $Position) {
            $Position->setzeGesamtpreisLocalized();
        }

        return $this;
    }

    /**
     * Gibt gesamte Warenkorbsumme lokalisiert als array zurueck.
     *
     * @return string[] - Gesamtsumme des Warenkorb
     */
    public function gibGesamtsummeWarenLocalized()
    {
        $WarensummeLocalized    = [];
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
            if ($Position->nAnzahl <= 0) {
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
        foreach ($this->PositionenArr as $Position) {
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
        $steuersatz = [];
        $steuerpos  = [];
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
            if ($position->kSteuerklasse <= 0) {
                continue;
            }
            $ust = gibUst($position->kSteuerklasse);
            if ($ust > 0) {
                $idx = array_search($ust, $steuersatz);
                if (!isset($steuerpos[$idx]->fBetrag)) {
                    $steuerpos[$idx]                  = new stdClass();
                    $steuerpos[$idx]->cName           = lang_steuerposition($ust, Session::CustomerGroup()->isMerchant());
                    $steuerpos[$idx]->fUst            = $ust;
                    $steuerpos[$idx]->fBetrag         = ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                    $steuerpos[$idx]->cPreisLocalized = gibPreisStringLocalized($steuerpos[$idx]->fBetrag);
                } else {
                    $steuerpos[$idx]->fBetrag        += ($position->fPreis * $position->nAnzahl * $ust) / 100.0;
                    $steuerpos[$idx]->cPreisLocalized = gibPreisStringLocalized($steuerpos[$idx]->fBetrag);
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
        foreach ($this->PositionenArr as $i => $oPosition) {
            if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_VERSANDPOS) {
                $this->PositionenArr[$i]->fPreisEinzelNetto = 0.0;
                $this->PositionenArr[$i]->fPreis            = 0.0;
                $this->PositionenArr[$i]->setzeGesamtpreisLocalized();
                break;
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
            if ($this->PositionenArr[$i]->kArtikel <= 0
                || $this->PositionenArr[$i]->Artikel->cLagerBeachten !== 'Y'
                || $this->PositionenArr[$i]->Artikel->cLagerKleinerNull === 'Y'
            ) {
                continue;
            }
            // Lagerbestand beachten und keine Überverkäufe möglich
            if (isset($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr)
                && !$this->PositionenArr[$i]->Artikel->kVaterArtikel
                && !$this->PositionenArr[$i]->Artikel->nIstVater
                && $this->PositionenArr[$i]->Artikel->cLagerVariation === 'Y'
                && count($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr) > 0
            ) {
                // Position mit Variationen, Lagerbestand in Variationen wird beachtet
                foreach ($this->PositionenArr[$i]->WarenkorbPosEigenschaftArr as $oWarenkorbPosEigenschaft) {
                    if ($oWarenkorbPosEigenschaft->kEigenschaftWert > 0 && $this->PositionenArr[$i]->nAnzahl > 0) {
                        //schaue in DB, ob Lagerbestand ausreichend
                        $oEigenschaftLagerbestand = Shop::DB()->query(
                            "SELECT kEigenschaftWert, fLagerbestand >= " . $this->PositionenArr[$i]->nAnzahl . " AS bAusreichend, fLagerbestand
                                FROM teigenschaftwert
                                WHERE kEigenschaftWert = " . (int)$oWarenkorbPosEigenschaft->kEigenschaftWert, 1
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
            } else {
                // Position ohne Variationen bzw. Variationen ohne eigenen Lagerbestand
                // schaue in DB, ob Lagerbestand ausreichend
                $oArtikelLagerbestand = Shop::DB()->query(
                    "SELECT kArtikel, fLagerbestand >= " . $this->PositionenArr[$i]->nAnzahl . " AS bAusreichend, fLagerbestand
                        FROM tartikel
                        WHERE kArtikel = " . (int)$this->PositionenArr[$i]->kArtikel, 1
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

        if ($bRedirect) {
            $this->setzePositionsPreise();
            $linkHelper = LinkHelper::getInstance();
            header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php') . '?fillOut=10', true, 303);
            exit;
        }

        return $this;
    }

    /**
     * Setzt Warenkorb mit Daten aus der DB mit spezifiziertem Primary Key
     *
     * @param int $kWarenkorb Primary Key
     * @return $this
     */
    public function loadFromDB($kWarenkorb)
    {
        $obj = Shop::DB()->select('twarenkorb', 'kWarenkorb', (int)$kWarenkorb);
        if ($obj !== null) {
            $members = array_keys(get_object_vars($obj));
            foreach ($members as $member) {
                $this->$member = $obj->$member;
            }
        }

        return $this;
    }

    /**
     * Fuegt Datensatz in DB ein. Primary Key wird in this gesetzt.
     *
     * @return int Key vom eingefuegten Warenkorb
     */
    public function insertInDB()
    {
        $obj = (object)[
            'kKunde'         => $this->kKunde,
            'kLieferadresse' => $this->kLieferadresse,
            'kZahlungsInfo'  => $this->kZahlungsInfo,
        ];
        if (!isset($obj->kZahlungsInfo) || $obj->kZahlungsInfo === '') {
            $obj->kZahlungsInfo = 0;
        }
        $this->kWarenkorb = Shop::DB()->insert('twarenkorb', $obj);

        return $this->kWarenkorb;
    }

    /**
     * Updatet Daten in der DB. Betroffen ist der Datensatz mit gleichem Primary Key
     *
     * @return int
     */
    public function updateInDB()
    {
        $obj = (object)[
            'kWarenkorb'     => $this->kWarenkorb,
            'kKunde'         => $this->kKunde,
            'kLieferadresse' => $this->kLieferadresse,
            'kZahlungsInfo'  => $this->kZahlungsInfo,
        ];

        return Shop::DB()->update('twarenkorb', 'kWarenkorb', $obj->kWarenkorb, $obj);
    }

    /**
     * @return string|mixed
     */
    public function getEstimatedDeliveryTime()
    {
        if (!is_array($this->PositionenArr) || count($this->PositionenArr) === 0) {
            return '';
        }
        $longestMinDeliveryDays = 0;
        $longestMaxDeliveryDays = 0;

        /** @var WarenkorbPos $oPosition */
        foreach ($this->PositionenArr as $oPosition) {
            if ($oPosition->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL || get_class($oPosition->Artikel) !== 'Artikel') {
                continue;
            }
            $oPosition->Artikel->getDeliveryTime($_SESSION['cLieferlandISO'], $oPosition->nAnzahl);
            WarenkorbPos::setEstimatedDelivery(
                $oPosition,
                $oPosition->Artikel->nMinDeliveryDays,
                $oPosition->Artikel->nMaxDeliveryDays
            );
            if (isset($oPosition->Artikel->nMinDeliveryDays)
                && $oPosition->Artikel->nMinDeliveryDays > $longestMinDeliveryDays
            ) {
                $longestMinDeliveryDays = $oPosition->Artikel->nMinDeliveryDays;
            }
            if (isset($oPosition->Artikel->nMaxDeliveryDays)
                && $oPosition->Artikel->nMaxDeliveryDays > $longestMaxDeliveryDays
            ) {
                $longestMaxDeliveryDays = $oPosition->Artikel->nMaxDeliveryDays;
            }
        }

        return getDeliverytimeEstimationText($longestMinDeliveryDays, $longestMaxDeliveryDays);
    }

    /**
     * @return object|null
     */
    public function gibLetztenWKArtikel()
    {
        $oResult = null;
        if (is_array($this->PositionenArr)) {
            $nZeitLetzteAenderung = 0;
            $positionCount        = count($this->PositionenArr) - 1;
            for ($i = $positionCount; $i >= 0; $i--) {
                if ($this->PositionenArr[$i]->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL
                    && $this->PositionenArr[$i]->kKonfigitem == 0
                ) {
                    if (isset($this->PositionenArr[$i]->nZeitLetzteAenderung)
                        && $this->PositionenArr[$i]->nZeitLetzteAenderung > $nZeitLetzteAenderung
                    ) {
                        $nZeitLetzteAenderung = $this->PositionenArr[$i]->nZeitLetzteAenderung;
                        $oResult              = $this->PositionenArr[$i]->Artikel;
                        ArtikelHelper::addVariationPictures($oResult, $this->PositionenArr[$i]->variationPicturesArr);
                    } elseif ($oResult === null) {
                        // Wenn keine nZeitLetzteAenderung gesetzt ist letztes Element des WK-Arrays nehmen
                        $oResult = $this->PositionenArr[$i]->Artikel;
                    }
                }
            }
        }

        return $oResult;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        $gewicht = 0;
        foreach ($this->PositionenArr as $pos) {
            $gewicht += $pos->fGesamtgewicht;
        }

        return $gewicht;
    }

    /**
     * @param bool $isRedirect
     * @param bool $unique
     */
    public function redirectTo($isRedirect = false, $unique = false)
    {
        if (!$isRedirect
            && !$unique
            && !isset($_SESSION['variBoxAnzahl_arr'])
            && $this->config['global']['global_warenkorb_weiterleitung'] === 'Y'
        ) {
            $linkHelper = LinkHelper::getInstance();
            header('Location: ' . $linkHelper->getStaticRoute('warenkorb.php'), true, 303);
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
        if (!isset($_SESSION['Kupon']->kKupon)) {
            return $isValid;
        }
        if ($this->posTypEnthalten(C_WARENKORBPOS_TYP_KUPON)) {
            // Kupon darf nicht im leeren Warenkorb eingelöst werden
            if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
                $Kupon = Shop::DB()->select('tkupon', 'kKupon', (int)$_SESSION['Kupon']->kKupon);
                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                    $isValid = (1 === angabenKorrekt(checkeKupon($Kupon)));
                    $this->updateCouponValue();
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
        } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren)
            && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
            && $_SESSION['Kupon']->cKuponTyp === 'standard'
            && $_SESSION['Kupon']->cWertTyp === 'prozent'
        ) {
            if (isset($_SESSION['Warenkorb']) && $this->gibAnzahlArtikelExt([C_WARENKORBPOS_TYP_ARTIKEL]) > 0) {
                $Kupon   = Shop::DB()->select('tkupon', 'kKupon', (int)$_SESSION['Kupon']->kKupon);
                $isValid = false;
                if (isset($Kupon->kKupon) && $Kupon->kKupon > 0 && $Kupon->cKuponTyp === 'standard') {
                    $isValid = (1 === angabenKorrekt(checkeKupon($Kupon)));
                }
            }
            if ($isValid === false) {
                unset($_SESSION['Kupon']);
                $this->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON)
                    ->setzePositionsPreise();
            }
        } elseif (isset($_SESSION['Kupon']->nGanzenWKRabattieren)
            && (int)$_SESSION['Kupon']->nGanzenWKRabattieren === 0
            && $_SESSION['Kupon']->cKuponTyp === 'standard'
        ) {
            //we have a coupon in the current session but none in the cart.
            //this happens with coupons tied to special articles that are no longer valid.
            unset($_SESSION['Kupon']);
        }

        return $isValid;
    }

    /**
     * update coupon value to avoid negative orders or coupon values under predefined value
     */
    public function updateCouponValue()
    {
        if (isset($_SESSION['Kupon']) && $_SESSION['Kupon']->cWertTyp === 'festpreis') {
            $Kupon         = $_SESSION['Kupon'];
            $maxPreisKupon = $Kupon->fWert;
            if ($Kupon->fWert > $this->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true)) {
                $maxPreisKupon = $this->gibGesamtsummeWarenExt([C_WARENKORBPOS_TYP_ARTIKEL], true);
            }
            $Spezialpos        = new stdClass();
            $Spezialpos->cName = [];
            foreach ($_SESSION['Sprachen'] as $Sprache) {
                $name_spr                          = Shop::DB()->select(
                    'tkuponsprache',
                    'kKupon', (int)$Kupon->kKupon,
                    'cISOSprache', $Sprache->cISO,
                    null, null,
                    false,
                    'cName'
                );
                $Spezialpos->cName[$Sprache->cISO] = $name_spr->cName;
            }
            $this->loescheSpezialPos(C_WARENKORBPOS_TYP_KUPON);
            $this->erstelleSpezialPos(
                $Spezialpos->cName,
                1,
                $maxPreisKupon * -1,
                $Kupon->kSteuerklasse,
                C_WARENKORBPOS_TYP_KUPON
            );
        }
    }

    /**
     * use summation rounding to even out discrepancies between total basket sum and sum of basket position totals
     * @param int $precision - decimal precision used (default 2)
     */
    public function useSummationRounding($precision = 2)
    {
        $cumulatedDelta    = 0;
        $cumulatedDeltaNet = 0;
        foreach (Session::Currencies() as $currency) {
            $currencyName = $currency->getName();
            foreach ($this->PositionenArr as $i => $position) {
                $grossAmount        = berechneBrutto(
                    $position->fPreis * $position->nAnzahl,
                    gibUst($position->kSteuerklasse),
                    12
                );
                $netAmount          = $position->fPreis * $position->nAnzahl;
                $roundedGrossAmount = berechneBrutto(
                    $position->fPreis * $position->nAnzahl + $cumulatedDelta,
                    gibUst($position->kSteuerklasse),
                    $precision
                );
                $roundedNetAmount   = round($position->fPreis * $position->nAnzahl + $cumulatedDeltaNet, $precision);

                if ($i !== 0 && $position->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                    if ($grossAmount != 0) {
                        $position->cGesamtpreisLocalized[0][$currencyName] = gibPreisStringLocalized($roundedGrossAmount, $currency);
                    }
                    if ($netAmount != 0) {
                        $position->cGesamtpreisLocalized[1][$currencyName] = gibPreisStringLocalized($roundedNetAmount, $currency);
                    }
                }
                $cumulatedDelta    += ($grossAmount - $roundedGrossAmount);
                $cumulatedDeltaNet += ($netAmount - $roundedNetAmount);
            }
        }
    }

    /**
     * @param Warenkorb $oWarenkorb
     * @return string
     */
    public static function getChecksum($oWarenkorb)
    {
        $checks = [
            'EstimatedDelivery' => isset($oWarenkorb->cEstimatedDelivery) ? $oWarenkorb->cEstimatedDelivery : '',
            'PositionenCount'   => isset($oWarenkorb->PositionenArr) ? count($oWarenkorb->PositionenArr) : 0,
            'PositionenArr'     => [],
        ];

        if (is_array($oWarenkorb->PositionenArr)) {
            foreach ($oWarenkorb->PositionenArr as $wkPos) {
                $checks['PositionenArr'][] = md5(serialize([
                    'kArtikel'          => isset($wkPos->kArtikel) ? $wkPos->kArtikel : 0,
                    'nAnzahl'           => isset($wkPos->nAnzahl) ? $wkPos->nAnzahl : 0,
                    'kVersandklasse'    => isset($wkPos->kVersandklasse) ? $wkPos->kVersandklasse : 0,
                    'nPosTyp'           => isset($wkPos->nPosTyp) ? $wkPos->nPosTyp : 0,
                    'fPreisEinzelNetto' => isset($wkPos->fPreisEinzelNetto) ? $wkPos->fPreisEinzelNetto : 0.0,
                    'fPreis'            => isset($wkPos->fPreis) ? $wkPos->fPreis : 0.0,
                    'cHinweis'          => isset($wkPos->cHinweis) ? $wkPos->cHinweis : '',
                ]));
            }
            sort($checks['PositionenArr']);
        }

        return md5(serialize($checks));
    }

    /**
     * refresh internal wk-checksum
     * @param object $oWarenkorb
     */
    public static function refreshChecksum($oWarenkorb)
    {
        $oWarenkorb->cChecksumme = self::getChecksum($oWarenkorb);
    }

    /**
     * Check if basket has digital products.
     * @return bool
     */
    public function hasDigitalProducts()
    {
        return class_exists('Download') && Download::hasDownloads($this);
    }
}
