<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Vergleichsliste
 */
class Vergleichsliste
{
    /**
     * @var array
     */
    public $oArtikel_arr = [];

    /**
     * Konstruktor
     *
     * @param int   $kArtikel - Falls angegeben, wird der Artikel mit angegebenem kArtikel aus der DB geholt
     * @param array $oVariationen_arr
     */
    public function __construct(int $kArtikel = 0, $oVariationen_arr = [])
    {
        if ($kArtikel > 0) {
            $oArtikel           = new stdClass();
            $tmpName            = Shop::Container()->getDB()->select(
                'tartikel',
                'kArtikel', $kArtikel,
                null, null,
                null, null,
                false,
                'cName'
            );
            $oArtikel->kArtikel = $kArtikel;
            $oArtikel->cName    = $tmpName->cName;
            if (is_array($oVariationen_arr) && count($oVariationen_arr) > 0) {
                $oArtikel->Variationen = $oVariationen_arr;
            }
            $this->oArtikel_arr[] = $oArtikel;

            executeHook(HOOK_VERGLEICHSLISTE_CLASS_EINFUEGEN);
        } elseif (isset($_SESSION['Vergleichsliste'])) {
            $this->oArtikel_arr = $_SESSION['Vergleichsliste']->oArtikel_arr;
        }
    }

    /**
     * Holt alle Artikel mit der aktuellen Sprache bzw Waehrung aus der DB und weißt sie neu der Session zu
     *
     * @return $this
     */
    public function umgebungsWechsel(): self
    {
        foreach ($_SESSION['Vergleichsliste']->oArtikel_arr as $i => $oArtikel) {
            $oArtikel_tmp           = new stdClass();
            $oArtikel_tmp->kArtikel = $oArtikel->kArtikel;
            $_SESSION['Vergleichsliste']->oArtikel_arr[$i] = $oArtikel_tmp;
        }

        return $this;
    }

    /**
     * @param int  $kArtikel
     * @param bool $bAufSession
     * @param int  $kKonfigitem
     * @return $this
     */
    public function fuegeEin(int $kArtikel, bool $bAufSession = true, int $kKonfigitem = 0): self
    {
        // Existiert der Key und ist er noch nicht vorhanden?
        if ($kArtikel > 0 && !$this->artikelVorhanden($kArtikel)) {
            //new slim variant for compare list
            $oArtikel = new Artikel();
            $oArtikel->kArtikel = $kArtikel;
            if ($kKonfigitem > 0 && class_exists('Konfigitem')) {
                // Falls Konfigitem gesetzt Preise + Name überschreiben
                $oKonfigitem = new Konfigitem($kKonfigitem);
                if ($oKonfigitem->getKonfigitem() > 0) {
                    $oArtikel->Preise->cVKLocalized[0] = $oKonfigitem->getPreisLocalized(true, false);
                    $oArtikel->Preise->cVKLocalized[1] = $oKonfigitem->getPreisLocalized(true, false, true);
                    $oArtikel->kSteuerklasse           = $oKonfigitem->getSteuerklasse();
                    unset($oArtikel->cLocalizedVPE);

                    if ($oKonfigitem->getUseOwnName()) {
                        $oArtikel->cName             = $oKonfigitem->getName();
                        $oArtikel->cBeschreibung     = $oKonfigitem->getBeschreibung();
                        $oArtikel->cKurzBeschreibung = $oKonfigitem->getBeschreibung();
                    }
                }
            }
            if ($oArtikel->kArtikel > 0) {
                $this->oArtikel_arr[] = $oArtikel;
            }
            if ($bAufSession) {
                $_SESSION['Vergleichsliste']->oArtikel_arr = $this->oArtikel_arr;
            }
        }

        return $this;
    }

    /**
     * @param int $kArtikel
     * @return bool
     */
    public function artikelVorhanden(int $kArtikel): bool
    {
        return \Functional\some($this->oArtikel_arr, function ($e) use ($kArtikel) {
            return (int)$e->kArtikel === $kArtikel;
        });
    }

    /**
     * @param Vergleichsliste $compareList
     * @return array
     * @former baueMerkmalundVariation()
     * @since 5.0.0
     */
    public static function buildAttributeAndVariation($compareList)
    {
        $Tmp_arr          = [];
        $oMerkmale_arr    = [];
        $oVariationen_arr = [];
        // Falls es min. einen Artikel in der Vergleichsliste gibt ...
        if (isset($compareList->oArtikel_arr) && count($compareList->oArtikel_arr) > 0) {
            // Alle Artikel in der Vergleichsliste durchgehen
            foreach ($compareList->oArtikel_arr as $oArtikel) {
                // Falls ein Artikel min. ein Merkmal besitzt
                if (isset($oArtikel->oMerkmale_arr) && count($oArtikel->oMerkmale_arr) > 0) {
                    // Falls das Merkmal Array nicht leer ist
                    if (count($oMerkmale_arr) > 0) {
                        foreach ($oArtikel->oMerkmale_arr as $oMerkmale) {
                            if (!self::containsAttribute($oMerkmale_arr, $oMerkmale->kMerkmal)) {
                                $oMerkmale_arr[] = $oMerkmale;
                            }
                        }
                    } else {
                        $oMerkmale_arr = $oArtikel->oMerkmale_arr;
                    }
                }
                // Falls ein Artikel min. eine Variation enthält
                if (isset($oArtikel->Variationen) && count($oArtikel->Variationen) > 0) {
                    if (count($oVariationen_arr) > 0) {
                        foreach ($oArtikel->Variationen as $oVariationen) {
                            if (!self::containsVariation($oVariationen_arr, $oVariationen->cName)) {
                                $oVariationen_arr[] = $oVariationen;
                            }
                        }
                    } else {
                        $oVariationen_arr = $oArtikel->Variationen;
                    }
                }
            }
        }

        $Tmp_arr[0] = $oMerkmale_arr;
        $Tmp_arr[1] = $oVariationen_arr;

        return $Tmp_arr;
    }

    /**
     * @param array $oMerkmale_arr
     * @param int   $kMerkmal
     * @return bool
     * @former istMerkmalEnthalten()
     * @since 5.0.0
     */
    public static function containsAttribute(array $oMerkmale_arr, int $kMerkmal): bool
    {
        return \Functional\some($oMerkmale_arr, function ($e) use ($kMerkmal) {
            return (int)$e->kMerkmal === $kMerkmal;
        });
    }

    /**
     * @param array  $oVariationen_arr
     * @param string $cName
     * @return bool
     * @former istVariationEnthalten()
     * @since 5.0.0
     */
    public static function containsVariation(array $oVariationen_arr, string $cName): bool
    {
        return \Functional\some($oVariationen_arr, function ($e) use ($cName) {
            return $e->cName === $cName;
        });
    }

    /**
     * @param array $exclude
     * @param array $config
     * @return string
     * @since 5.0.0
     */
    public static function gibMaxPrioSpalteV($exclude, $config)
    {
        $nMax     = 0;
        $cElement = '';
        $conf     = $config['vergleichsliste'];
        if ($conf['vergleichsliste_artikelnummer'] > $nMax && !in_array('cArtNr', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_artikelnummer'];
            $cElement = 'cArtNr';
        }
        if ($conf['vergleichsliste_hersteller'] > $nMax && !in_array('cHersteller', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_hersteller'];
            $cElement = 'cHersteller';
        }
        if ($conf['vergleichsliste_beschreibung'] > $nMax && !in_array('cBeschreibung', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_beschreibung'];
            $cElement = 'cBeschreibung';
        }
        if ($conf['vergleichsliste_kurzbeschreibung'] > $nMax && !in_array('cKurzBeschreibung', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_kurzbeschreibung'];
            $cElement = 'cKurzBeschreibung';
        }
        if ($conf['vergleichsliste_artikelgewicht'] > $nMax && !in_array('fArtikelgewicht', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_artikelgewicht'];
            $cElement = 'fArtikelgewicht';
        }
        if ($conf['vergleichsliste_versandgewicht'] > $nMax && !in_array('fGewicht', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_versandgewicht'];
            $cElement = 'fGewicht';
        }
        if ($conf['vergleichsliste_merkmale'] > $nMax && !in_array('Merkmale', $exclude, true)) {
            $nMax     = $conf['vergleichsliste_merkmale'];
            $cElement = 'Merkmale';
        }
        if ($conf['vergleichsliste_variationen'] > $nMax && !in_array('Variationen', $exclude, true)) {
            $cElement = 'Variationen';
        }

        return $cElement;
    }

    /**
     * Fügt nach jedem Preisvergleich eine Statistik in die Datenbank.
     * Es sind allerdings nur 3 Einträge pro IP und Tag möglich
     *
     * @param Vergleichsliste $compareList
     */
    public static function setComparison($compareList)
    {
        if (isset($compareList->oArtikel_arr)
            && is_array($compareList->oArtikel_arr)
            && count($compareList->oArtikel_arr) > 0
        ) {
            $nVergleiche = Shop::Container()->getDB()->queryPrepared(
                'SELECT count(kVergleichsliste) AS nVergleiche
                    FROM tvergleichsliste
                    WHERE cIP = :ip
                        AND dDate > DATE_SUB(now(),INTERVAL 1 DAY)',
                ['ip' => RequestHelper::getIP()],
                \DB\ReturnType::SINGLE_OBJECT
            );

            if ($nVergleiche->nVergleiche < 3) {
                $compareListTable        = new stdClass();
                $compareListTable->cIP   = RequestHelper::getIP();
                $compareListTable->dDate = date('Y-m-d H:i:s');
                $kVergleichsliste = Shop::Container()->getDB()->insert('tvergleichsliste', $compareListTable);
                foreach ($compareList->oArtikel_arr as $oArtikel) {
                    $compareListPosTable                   = new stdClass();
                    $compareListPosTable->kVergleichsliste = $kVergleichsliste;
                    $compareListPosTable->kArtikel         = $oArtikel->kArtikel;
                    $compareListPosTable->cArtikelName     = $oArtikel->cName;

                    Shop::Container()->getDB()->insert('tvergleichslistepos', $compareListPosTable);
                }
            }
        }
    }
}
