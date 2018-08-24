<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class SearchSpecialHelper
 */
class SearchSpecialHelper
{
    /**
     * @param int $langID
     * @return array|mixed
     * @former holeAlleSuchspecialOverlays()
     * @since 5.0.0
     */
    public static function getAll(int $langID = 0)
    {
        $langID  = $langID > 0 ? $langID : Shop::getLanguageID();
        $cacheID = 'haso_' . $langID;
        if (($overlays = Shop::Cache()->get($cacheID)) === false) {
            $ssoList = Shop::Container()->getDB()->query(
                "SELECT tsuchspecialoverlay.*, tsuchspecialoverlaysprache.kSprache,
                    tsuchspecialoverlaysprache.cBildPfad, tsuchspecialoverlaysprache.nAktiv,
                    tsuchspecialoverlaysprache.nPrio, tsuchspecialoverlaysprache.nMargin,
                    tsuchspecialoverlaysprache.nTransparenz,
                    tsuchspecialoverlaysprache.nGroesse, tsuchspecialoverlaysprache.nPosition
                    FROM tsuchspecialoverlay
                    JOIN tsuchspecialoverlaysprache
                        ON tsuchspecialoverlaysprache.kSuchspecialOverlay = tsuchspecialoverlay.kSuchspecialOverlay
                        AND tsuchspecialoverlaysprache.kSprache = " . $langID . "
                    WHERE tsuchspecialoverlaysprache.nAktiv = 1
                        AND tsuchspecialoverlaysprache.nPrio > 0
                    ORDER BY tsuchspecialoverlaysprache.nPrio DESC",
                \DB\ReturnType::ARRAY_OF_OBJECTS
            );

            $overlays = [];
            foreach ($ssoList as $sso) {
                $sso->kSuchspecialOverlay = (int)$sso->kSuchspecialOverlay;
                $sso->nAktiv              = (int)$sso->nAktiv;
                $sso->nPrio               = (int)$sso->nPrio;
                $sso->nMargin             = (int)$sso->nMargin;
                $sso->nTransparenz        = (int)$sso->nTransparenz;
                $sso->nGroesse            = (int)$sso->nGroesse;
                $sso->nPosition           = (int)$sso->nPosition;

                $idx = strtolower(str_replace([' ', '-', '_'], '', $sso->cSuchspecial));
                $idx = preg_replace(
                    ['/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/', '/ß/'],
                    ['ae', 'oe', 'ue', 'ae', 'oe', 'ue', 'ss'],
                    $idx
                );
                $overlays[$idx]              = $sso;
                $overlays[$idx]->cPfadKlein  = PFAD_SUCHSPECIALOVERLAY_KLEIN . $overlays[$idx]->cBildPfad;
                $overlays[$idx]->cPfadNormal = PFAD_SUCHSPECIALOVERLAY_NORMAL . $overlays[$idx]->cBildPfad;
                $overlays[$idx]->cPfadGross  = PFAD_SUCHSPECIALOVERLAY_GROSS . $overlays[$idx]->cBildPfad;
            }
            Shop::Cache()->set($cacheID, $overlays, [CACHING_GROUP_OPTION]);
        }

        return $overlays;
    }

    /**
     * @return array
     * @former baueAlleSuchspecialURLs
     * @since 5.0.0
     */
    public static function buildAllURLs(): array
    {
        $overlays = [];

        $overlays[SEARCHSPECIALS_BESTSELLER]        = new stdClass();
        $overlays[SEARCHSPECIALS_BESTSELLER]->cName = Shop::Lang()->get('bestseller');
        $overlays[SEARCHSPECIALS_BESTSELLER]->cURL  = self::buildURL(SEARCHSPECIALS_BESTSELLER);

        $overlays[SEARCHSPECIALS_SPECIALOFFERS]        = new stdClass();
        $overlays[SEARCHSPECIALS_SPECIALOFFERS]->cName = Shop::Lang()->get('specialOffers');
        $overlays[SEARCHSPECIALS_SPECIALOFFERS]->cURL  = self::buildURL(SEARCHSPECIALS_SPECIALOFFERS);

        $overlays[SEARCHSPECIALS_NEWPRODUCTS]        = new stdClass();
        $overlays[SEARCHSPECIALS_NEWPRODUCTS]->cName = Shop::Lang()->get('newProducts');
        $overlays[SEARCHSPECIALS_NEWPRODUCTS]->cURL  = self::buildURL(SEARCHSPECIALS_NEWPRODUCTS);

        $overlays[SEARCHSPECIALS_TOPOFFERS]        = new stdClass();
        $overlays[SEARCHSPECIALS_TOPOFFERS]->cName = Shop::Lang()->get('topOffers');
        $overlays[SEARCHSPECIALS_TOPOFFERS]->cURL  = self::buildURL(SEARCHSPECIALS_TOPOFFERS);

        $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]        = new stdClass();
        $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]->cName = Shop::Lang()->get('upcomingProducts');
        $overlays[SEARCHSPECIALS_UPCOMINGPRODUCTS]->cURL  = self::buildURL(SEARCHSPECIALS_UPCOMINGPRODUCTS);

        $overlays[SEARCHSPECIALS_TOPREVIEWS]        = new stdClass();
        $overlays[SEARCHSPECIALS_TOPREVIEWS]->cName = Shop::Lang()->get('topReviews');
        $overlays[SEARCHSPECIALS_TOPREVIEWS]->cURL  = self::buildURL(SEARCHSPECIALS_TOPREVIEWS);

        return $overlays;
    }

    /**
     * @param int $kKey
     * @return mixed|string
     * @former baueSuchSpecialURL()
     * @since 5.0.0
     */
    public static function buildURL(int $kKey)
    {
        $cacheID = 'bsurl_' . $kKey . '_' . Shop::getLanguageID();
        if (($url = Shop::Cache()->get($cacheID)) !== false) {
            executeHook(HOOK_BOXEN_INC_SUCHSPECIALURL);

            return $url;
        }
        $oSeo = Shop::Container()->getDB()->select(
            'tseo',
            'kSprache', Shop::getLanguageID(),
            'cKey', 'suchspecial',
            'kKey', $kKey,
            false,
            'cSeo'
        ) ?? new stdClass();

        $oSeo->kSuchspecial = $kKey;
        executeHook(HOOK_BOXEN_INC_SUCHSPECIALURL);
        $url = UrlHelper::buildURL($oSeo, URLART_SEARCHSPECIALS);
        Shop::Cache()->set($cacheID, $url, [CACHING_GROUP_CATEGORY]);

        return $url;
    }

    /**
     * @return string
     * @former gibVaterSQL()
     * @since 5.0.0
     */
    public static function getParentSQL(): string
    {
        return ' AND tartikel.kVaterArtikel = 0';
    }

    /**
     * @param array $arr
     * @param int   $limit
     * @return array
     * @former randomizeAndLimit()
     * @since 5.0.0
     */
    public static function randomizeAndLimit(array $arr, int $limit = 1): array
    {
        shuffle($arr);

        return array_slice($arr, 0, $limit);
    }

    /**
     * @param int $nLimit
     * @param int $kKundengruppe
     * @return array
     * @former gibTopAngebote()
     * @since 5.0.0
     */
    public static function getTopOffers(int $nLimit = 20, int $kKundengruppe = 0): array
    {
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $topArticles = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cTopArtikel = 'Y'
                    " . self::getParentSQL() . "
                    " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($topArticles, min(count($topArticles), $nLimit));
    }

    /**
     * @param int $nLimit
     * @param int $kKundengruppe
     * @return array
     * @former gibBestseller()
     * @since 5.0.0
     */
    public static function getBestsellers(int $nLimit = 20, int $kKundengruppe = 0): array
    {
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $oGlobalnEinstellung_arr = Shop::getSettings([CONF_GLOBAL]);
        $nSchwelleBestseller     = isset($oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl'])
            ? (float)$oGlobalnEinstellung_arr['global']['global_bestseller_minanzahl']
            : 10;
        $bestsellers = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel, tbestseller.fAnzahl
                FROM tbestseller, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tbestseller.kArtikel = tartikel.kArtikel
                    AND round(tbestseller.fAnzahl) >= " . $nSchwelleBestseller . "
                    " . self::getParentSQL() . "
                    " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . "
                ORDER BY fAnzahl DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($bestsellers, min(count($bestsellers), $nLimit));
    }

    /**
     * @param int $nLimit
     * @param int $kKundengruppe
     * @return array
     * @former gibSonderangebote()
     * @since 5.0.0
     */
    public static function getSpecialOffers(int $nLimit = 20, int $kKundengruppe = 0): array
    {
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $specialOffers = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
                FROM tartikel
                JOIN tartikelsonderpreis 
                    ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                JOIN tsonderpreise 
                    ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                    AND tsonderpreise.kKundengruppe = " . $kKundengruppe . "
                    AND tartikelsonderpreis.cAktiv = 'Y'
                    AND tartikelsonderpreis.dStart <= now()
                    AND (tartikelsonderpreis.dEnde >= CURDATE() OR tartikelsonderpreis.dEnde = '0000-00-00')
                    AND (tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                    " . self::getParentSQL() . "
                    " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($specialOffers, min(count($specialOffers), $nLimit));
    }

    /**
     * @param int $nLimit
     * @param int $kKundengruppe
     * @return array
     * @former gibNeuImSortiment()
     * @since 5.0.0
     */
    public static function getNewProducts(int $nLimit, int $kKundengruppe = 0): array
    {
        if (!$nLimit) {
            $nLimit = 20;
        }
        if (!$kKundengruppe) {
            $kKundengruppe = Kundengruppe::getDefaultGroupID();
        }
        $config     = Shop::getSettings([CONF_BOXEN]);
        $nAlterTage = ($config['boxen']['box_neuimsortiment_alter_tage'] > 0)
            ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
            : 30;
        $new = Shop::Container()->getDB()->query(
            "SELECT tartikel.kArtikel
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = " . $kKundengruppe . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cNeu = 'Y'
                    AND dErscheinungsdatum <= now()
                    AND DATE_SUB(now(), INTERVAL " . $nAlterTage . " DAY) < tartikel.dErstellt
                    " . self::getParentSQL() . "
                    " . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($new, min(count($new), $nLimit));
    }

}
