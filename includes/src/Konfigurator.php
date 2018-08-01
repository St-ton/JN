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
        private static $oGruppen_arr = [];

        /**
         * getKonfig
         *
         * @param int $kArtikel
         * @param int $kSprache
         * @return array
         */
        public static function getKonfig(int $kArtikel, int $kSprache = 0): array
        {
            if (isset(self::$oGruppen_arr[$kArtikel])) {
                //#7482
                return self::$oGruppen_arr[$kArtikel];
            }
            $oGruppen_arr = Shop::Container()->getDB()->selectAll(
                'tartikelkonfiggruppe',
                'kArtikel',
                $kArtikel,
                'kArtikel, kKonfigGruppe',
                'nSort ASC'
            );
            if (!is_array($oGruppen_arr) || count($oGruppen_arr) === 0) {
                return [];
            }
            if (!$kSprache) {
                $kSprache = Shop::getLanguageID();
            }
            foreach ($oGruppen_arr as &$oGruppe) {
                $oGruppe = new Konfiggruppe($oGruppe->kKonfigGruppe, $kSprache);
            }
            unset($oGruppe);

            self::$oGruppen_arr[$kArtikel] = $oGruppen_arr;

            return $oGruppen_arr;
        }

        /**
         * @param int $kArtikel
         * @return bool
         */
        public static function hasKonfig(int $kArtikel): bool
        {
            $oGruppen_arr = Shop::Container()->getDB()->query(
                "SELECT kArtikel, kKonfigGruppe
                     FROM tartikelkonfiggruppe
                     WHERE tartikelkonfiggruppe.kArtikel = " . $kArtikel . "
                     ORDER BY tartikelkonfiggruppe.nSort ASC",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            return is_array($oGruppen_arr) && count($oGruppen_arr) > 0;
        }

        /**
         * @param int $kArtikel
         * @return bool
         */
        public static function validateKonfig($kArtikel): bool
        {
            /* Vorvalidierung deaktiviert */
            return true;
        }

        /**
         * @param object $oBasket
         */
        public static function postcheckBasket(&$oBasket)
        {
            if (!is_array($oBasket->PositionenArr) || count($oBasket->PositionenArr) === 0) {
                return;
            }
            $beDeletednPos_arr = [];
            foreach ($oBasket->PositionenArr as $nPos => $oPosition) {
                $bDeleted = false;
                if ($oPosition->nPosTyp === C_WARENKORBPOS_TYP_ARTIKEL) {
                    // Konfigvater
                    if ($oPosition->cUnique && $oPosition->kKonfigitem == 0) {
                        $oKonfigitem_arr = [];

                        // Alle Kinder suchen
                        foreach ($oBasket->PositionenArr as $oChildPosition) {
                            if ($oChildPosition->cUnique &&
                                $oChildPosition->cUnique === $oPosition->cUnique
                                && $oChildPosition->kKonfigitem > 0
                            ) {
                                $oKonfigitem_arr[] = new Konfigitem($oChildPosition->kKonfigitem);
                            }
                        }

                        // Konfiguration validieren
                        if (self::validateBasket($oPosition->kArtikel, $oKonfigitem_arr) !== true) {
                            $bDeleted = true;
                            $beDeletednPos_arr[] = $nPos;
                            //loescheWarenkorbPosition($nPos);
                        }
                    } // Standardartikel ebenfalls auf eine mögliche Konfiguration prüfen
                    elseif (!$oPosition->cUnique) {
                        // Konfiguration vorhanden -> löschen
                        if (self::hasKonfig($oPosition->kArtikel)) {
                            $bDeleted = true;
                            $beDeletednPos_arr[] = $nPos;
                            //loescheWarenkorbPosition($nPos);
                        }
                    }

                    if ($bDeleted) {
                        $cISO = $_SESSION['cISOSprache'];
                        Shop::Container()->getLogService()->error(
                            'Validierung der Konfiguration fehlgeschlagen - Warenkorbposition wurde entfernt: ' .
                            $oPosition->cName[$cISO] . '(' . $oPosition->kArtikel . ')'
                        );
                    }
                }
            }
            WarenkorbHelper::deleteCartPositions($beDeletednPos_arr);
        }

        /**
         * @param int   $kArtikel
         * @param array $oKonfigitem_arr
         * @return array|bool
         */
        public static function validateBasket(int $kArtikel, $oKonfigitem_arr)
        {
            if ($kArtikel === 0 || !is_array($oKonfigitem_arr)) {
                Shop::Container()->getLogService()->error('Validierung der Konfiguration fehlgeschlagen - Ungültige Daten');

                return false;
            }
            // Gesamtpreis
            $fFinalPrice = 0.0;
            // Hauptartikel
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($kArtikel, Artikel::getDefaultOptions());
            // Grundpreis
            if ($oArtikel && (int)$oArtikel->kArtikel > 0) {
                $fFinalPrice += $oArtikel->Preise->fVKNetto;
            }
            // Anzahl
            foreach ($oKonfigitem_arr as $oKonfigitem) {
                if (!isset($oKonfigitem->fAnzahl) ||
                    $oKonfigitem->fAnzahl < $oKonfigitem->getMin() ||
                    $oKonfigitem->fAnzahl > $oKonfigitem->getMax()) {
                    $oKonfigitem->fAnzahl = $oKonfigitem->getInitial();
                }
                $fFinalPrice += $oKonfigitem->getPreis(true) * $oKonfigitem->fAnzahl;
            }

            $aError_arr = [];
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
                        $aError_arr[$kKonfiggruppe] =
                            Shop::Lang()->get('configChooseNComponents', 'productDetails', $oGruppe->getMin());
                    } else {
                        $aError_arr[$kKonfiggruppe] =
                            Shop::Lang()->get('configChooseMinComponents', 'productDetails', $oGruppe->getMin());
                    }
                    $aError_arr[$kKonfiggruppe] .= self::langComponent($oGruppe->getMin() > 1);
                } elseif ($nItemCount > $oGruppe->getMax() && $oGruppe->getMax() > 0) {
                    if ($oGruppe->getMin() == $oGruppe->getMax()) {
                        $aError_arr[$kKonfiggruppe] =
                            Shop::Lang()->get('configChooseNComponents', 'productDetails', $oGruppe->getMin()) .
                            self::langComponent($oGruppe->getMin() > 1);
                    } else {
                        $aError_arr[$kKonfiggruppe] =
                            Shop::Lang()->get('configChooseMaxComponents', 'productDetails', $oGruppe->getMax()) .
                            self::langComponent($oGruppe->getMax() > 1);
                    }
                }
            }

            if ($fFinalPrice < 0.0) {
                $cError = sprintf(
                    "Negative Konfigurationssumme für Artikel '%s' (Art.Nr.: %s, Netto: %s) - Vorgang wurde abgebrochen",
                    $oArtikel->cName, $oArtikel->cArtNr, Preise::getLocalizedPriceString($fFinalPrice)
                );
                Shop::Container()->getLogService()->error($cError);

                return false;
            }

            return count($aError_arr) === 0 ? true : $aError_arr;
        }

        /**
         * @param bool $bPlural
         * @param bool $bSpace
         * @return string
         */
        private static function langComponent(bool $bPlural = false, bool $bSpace = true): string
        {
            $cComponent = $bSpace ? ' ' : '';

            return $cComponent . Shop::Lang()->get($bPlural ? 'configComponents' : 'configComponent', 'productDetails');
        }
    }
}
