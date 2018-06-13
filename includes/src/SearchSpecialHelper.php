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

        // URLs bauen
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
}
