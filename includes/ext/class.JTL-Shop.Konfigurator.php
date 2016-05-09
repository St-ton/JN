<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_KONFIGURATOR)) {
    /**
     * Class Konfigurator
     */
    class Konfigurator
    {
        /**
         * @var array
         */
        private static $oGruppen_arr = array();

        /**
         * getKonfig
         *
         * @param int $kArtikel
         * @param int $kSprache
         * @return array
         * @access public
         */
        public static function getKonfig($kArtikel, $kSprache = 0)
        {
            if (isset(self::$oGruppen_arr[$kArtikel])) {
                //#7482
                return self::$oGruppen_arr[$kArtikel];
            }
            $oGruppen_arr = Shop::DB()->query(
                "SELECT kArtikel, kKonfigGruppe
                    FROM tartikelkonfiggruppe
                    WHERE tartikelkonfiggruppe.kArtikel = '" . intval($kArtikel) . "'
                    ORDER BY tartikelkonfiggruppe.nSort
                    ASC", 2
            );

            if (!is_array($oGruppen_arr) || count($oGruppen_arr) === 0) {
                return;
            }
            if (!$kSprache) {
                $kSprache = $_SESSION['kSprache'];
            }
            foreach ($oGruppen_arr as &$oGruppe) {
                $oGruppe = new Konfiggruppe($oGruppe->kKonfigGruppe, $kSprache);
            }

            self::$oGruppen_arr[$kArtikel] = $oGruppen_arr;

            return $oGruppen_arr;
        }

        /**
         * @param $kArtikel
         * @return bool
         */
        public static function hasKonfig($kArtikel)
        {
            $oGruppen_arr = Shop::DB()->query(
                "SELECT kArtikel, kKonfigGruppe
                    FROM tartikelkonfiggruppe
                    WHERE tartikelkonfiggruppe.kArtikel = " . intval($kArtikel) . "
                    ORDER BY tartikelkonfiggruppe.nSort
                    ASC", 2
            );

            return (is_array($oGruppen_arr) && count($oGruppen_arr) > 0);
        }

        /**
         * @param int $kArtikel
         * @return bool
         */
        public static function validateKonfig($kArtikel)
        {
            /* Vorvalidierung deaktiviert */
            return true;
        }

        /**
         * @param object $oBasket
         */
        public static function postcheckBasket(&$oBasket)
        {
            // TODO: bIgnoreLimits
            // REF: $_POST['konfig_ignore_limits']

            if (!function_exists('loescheWarenkorbPosition')) {
                require_once PFAD_INCLUDES . 'warenkorb_inc.php';
            }

            if (is_array($oBasket->PositionenArr) && count($oBasket->PositionenArr) > 0) {
                foreach ($oBasket->PositionenArr as $nPos => $oPosition) {
                    $bDeleted = false;
                    if ($oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL) {
                        // Konfigvater
                        if ($oPosition->cUnique && $oPosition->kKonfigitem == 0) {
                            $oKonfigitem_arr = array();

                            // Alle Kinder suchen
                            foreach ($oBasket->PositionenArr as $oChildPosition) {
                                if ($oChildPosition->cUnique && $oChildPosition->cUnique == $oPosition->cUnique && $oChildPosition->kKonfigitem > 0) {
                                    $oKonfigitem_arr[] = new Konfigitem($oChildPosition->kKonfigitem);
                                }
                            }

                            // Konfiguration validieren
                            if (($aError_arr = self::validateBasket($oPosition->kArtikel, $oKonfigitem_arr)) !== true) {
                                $bDeleted = true;
                                loescheWarenkorbPosition($nPos);
                            }
                        } // Standardartikel ebenfalls auf eine mögliche Konfiguration prüfen
                        elseif (!$oPosition->cUnique) {
                            // Konfiguration vorhanden -> löschen
                            if (self::hasKonfig($oPosition->kArtikel)) {
                                $bDeleted = true;
                                loescheWarenkorbPosition($nPos);
                            }
                        }

                        if ($bDeleted) {
                            // $Warenkorbhinweise
                            $cISO = $_SESSION['cISOSprache'];
                            Jtllog::writeLog("Validierung der Konfiguration fehlgeschlagen - Warenkorbposition wurde entfernt: {$oPosition->cName[$cISO]} ({$oPosition->kArtikel})", JTLLOG_LEVEL_ERROR);
                        }
                    }
                }
            }
        }

        /**
         * @param int   $kArtikel
         * @param array $oKonfigitem_arr
         * @return array|bool
         */
        public static function validateBasket($kArtikel, $oKonfigitem_arr)
        {
            if (intval($kArtikel) === 0 || !is_array($oKonfigitem_arr)) {
                Jtllog::writeLog(utf8_decode('Validierung der Konfiguration fehlgeschlagen - Ungültige Daten'), JTLLOG_LEVEL_ERROR);

                return false;
            }
            // Gesamtpreis
            $fFinalPrice = 0.0;
            // Hauptartikel
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            // Grundpreis
            if ($oArtikel && intval($oArtikel->kArtikel) > 0) {
                $fFinalPrice += $oArtikel->Preise->fVKNetto;
            }
            // Anzahl
            foreach ($oKonfigitem_arr as $oKonfigitem) {
                if (!isset($oKonfigitem->fAnzahl) || $oKonfigitem->fAnzahl < $oKonfigitem->getMin() || $oKonfigitem->fAnzahl > $oKonfigitem->getMax()) {
                    $oKonfigitem->fAnzahl = $oKonfigitem->getInitial();
                }
                $fFinalPrice += $oKonfigitem->getPreis(true) * $oKonfigitem->fAnzahl;
            }

            $aError_arr = array();
            foreach (self::getKonfig($kArtikel) as $oGruppe) {
                $nItemCount    = 0;
                $kKonfiggruppe = $oGruppe->getKonfiggruppe();
                foreach ($oKonfigitem_arr as $oKonfigitem) {
                    if ($oKonfigitem->getKonfiggruppe() == $kKonfiggruppe) {
                        $nItemCount++;
                    }
                }
                if ($nItemCount < $oGruppe->getMin() && $oGruppe->getMin() > 0) {
                    if ($oGruppe->getMin() == $oGruppe->getMax()) {
                        $aError_arr[$kKonfiggruppe] = Shop::Lang()->get('configChooseNComponents', 'productDetails', $oGruppe->getMin());
                    } else {
                        $aError_arr[$kKonfiggruppe] = Shop::Lang()->get('configChooseMinComponents', 'productDetails', $oGruppe->getMin());
                    }
                    $aError_arr[$kKonfiggruppe] .= self::langComponent($oGruppe->getMin() > 1);
                } elseif ($nItemCount > $oGruppe->getMax() && $oGruppe->getMax() > 0) {
                    if ($oGruppe->getMin() == $oGruppe->getMax()) {
                        $aError_arr[$kKonfiggruppe] = Shop::Lang()->get('configChooseNComponents', 'productDetails', $oGruppe->getMin()) . self::langComponent($oGruppe->getMin() > 1);
                    } else {
                        $aError_arr[$kKonfiggruppe] = Shop::Lang()->get('configChooseMaxComponents', 'productDetails', $oGruppe->getMax()) . self::langComponent($oGruppe->getMax() > 1);
                    }
                }
            }

            if ($fFinalPrice < 0.0) {
                $cError = sprintf(
                    "Negative Konfigurationssumme für Artikel '%s' (Art.Nr.: %s, Netto: %s) - Vorgang wurde abgebrochen",
                    $oArtikel->cName, $oArtikel->cArtNr, gibPreisStringLocalized($fFinalPrice)
                );
                Jtllog::writeLog($cError, JTLLOG_LEVEL_ERROR);

                return false;
            }

            return count($aError_arr) === 0 ? true : $aError_arr;
        }

        /**
         * @param bool $bPlural
         * @param bool $bSpace
         * @return string
         */
        private static function langComponent($bPlural = false, $bSpace = true)
        {
            $cComponent = $bSpace ? ' ' : '';
            $cComponent .= ($bPlural) ?
                Shop::Lang()->get('configComponents', 'productDetails') :
                Shop::Lang()->get('configComponent', 'productDetails');

            return $cComponent;
        }
    }
}
