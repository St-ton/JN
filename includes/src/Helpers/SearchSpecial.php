<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Helpers;

use JTL\Customer\CustomerGroup;
use JTL\DB\ReturnType;
use JTL\Media\Image\Overlay;
use JTL\Shop;
use stdClass;

/**
 * Class SearchSpecial
 * @package JTL\Helpers
 * @since 5.0.0
 */
class SearchSpecial
{
    /**
     * @param int $langID
     * @return Overlay[]
     * @former holeAlleSuchspecialOverlays()
     * @since 5.0.0
     */
    public static function getAll(int $langID = 0): array
    {
        $langID  = $langID > 0 ? $langID : Shop::getLanguageID();
        $cacheID = 'haso_' . $langID;
        if (($overlays = Shop::Container()->getCache()->get($cacheID)) === false) {
            $overlays = [];
            $types    = Shop::Container()->getDB()->query(
                'SELECT kSuchspecialOverlay
                    FROM tsuchspecialoverlay',
                ReturnType::ARRAY_OF_OBJECTS
            );
            foreach ($types as $type) {
                $overlay = Overlay::getInstance((int)$type->kSuchspecialOverlay, $langID);
                if ($overlay->getActive() === 1) {
                    $overlays[] = $overlay;
                }
            }
            $overlays = \Functional\sort($overlays, static function (Overlay $left, Overlay $right) {
                return $left->getPriority() > $right->getPriority();
            });
            Shop::Container()->getCache()->set($cacheID, $overlays, [\CACHING_GROUP_OPTION]);
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

        $overlays[\SEARCHSPECIALS_BESTSELLER]        = new stdClass();
        $overlays[\SEARCHSPECIALS_BESTSELLER]->cName = Shop::Lang()->get('bestseller');
        $overlays[\SEARCHSPECIALS_BESTSELLER]->cURL  = self::buildURL(\SEARCHSPECIALS_BESTSELLER);

        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]->cName = Shop::Lang()->get('specialOffers');
        $overlays[\SEARCHSPECIALS_SPECIALOFFERS]->cURL  = self::buildURL(\SEARCHSPECIALS_SPECIALOFFERS);

        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]->cName = Shop::Lang()->get('newProducts');
        $overlays[\SEARCHSPECIALS_NEWPRODUCTS]->cURL  = self::buildURL(\SEARCHSPECIALS_NEWPRODUCTS);

        $overlays[\SEARCHSPECIALS_TOPOFFERS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_TOPOFFERS]->cName = Shop::Lang()->get('topOffers');
        $overlays[\SEARCHSPECIALS_TOPOFFERS]->cURL  = self::buildURL(\SEARCHSPECIALS_TOPOFFERS);

        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]->cName = Shop::Lang()->get('upcomingProducts');
        $overlays[\SEARCHSPECIALS_UPCOMINGPRODUCTS]->cURL  = self::buildURL(\SEARCHSPECIALS_UPCOMINGPRODUCTS);

        $overlays[\SEARCHSPECIALS_TOPREVIEWS]        = new stdClass();
        $overlays[\SEARCHSPECIALS_TOPREVIEWS]->cName = Shop::Lang()->get('topReviews');
        $overlays[\SEARCHSPECIALS_TOPREVIEWS]->cURL  = self::buildURL(\SEARCHSPECIALS_TOPREVIEWS);

        return $overlays;
    }

    /**
     * @param int $key
     * @return mixed|string
     * @former baueSuchSpecialURL()
     * @since 5.0.0
     */
    public static function buildURL(int $key)
    {
        $cacheID = 'bsurl_' . $key . '_' . Shop::getLanguageID();
        if (($url = Shop::Container()->getCache()->get($cacheID)) !== false) {
            \executeHook(\HOOK_BOXEN_INC_SUCHSPECIALURL);

            return $url;
        }
        $oSeo = Shop::Container()->getDB()->select(
            'tseo',
            'kSprache',
            Shop::getLanguageID(),
            'cKey',
            'suchspecial',
            'kKey',
            $key,
            false,
            'cSeo'
        ) ?? new stdClass();

        $oSeo->kSuchspecial = $key;
        \executeHook(\HOOK_BOXEN_INC_SUCHSPECIALURL);
        $url = URL::buildURL($oSeo, \URLART_SEARCHSPECIALS);
        Shop::Container()->getCache()->set($cacheID, $url, [\CACHING_GROUP_CATEGORY]);

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
        \shuffle($arr);

        return \array_slice($arr, 0, $limit);
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return array
     * @former gibTopAngebote()
     * @since 5.0.0
     */
    public static function getTopOffers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $top = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cTopArtikel = 'Y'
                    " . self::getParentSQL() . '
                    ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($top, \min(\count($top), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return array
     * @former gibBestseller()
     * @since 5.0.0
     */
    public static function getBestsellers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $config      = Shop::getSettings([\CONF_GLOBAL]);
        $minAmount   = isset($config['global']['global_bestseller_minanzahl'])
            ? (float)$config['global']['global_bestseller_minanzahl']
            : 10;
        $bestsellers = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, tbestseller.fAnzahl
                FROM tbestseller, tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tbestseller.kArtikel = tartikel.kArtikel
                    AND round(tbestseller.fAnzahl) >= ' . $minAmount . '
                    ' . self::getParentSQL() . '
                    ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL() . '
                ORDER BY fAnzahl DESC',
            ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($bestsellers, \min(\count($bestsellers), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return array
     * @former gibSonderangebote()
     * @since 5.0.0
     */
    public static function getSpecialOffers(int $limit = 20, int $customerGroupID = 0): array
    {
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $specialOffers = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel, tsonderpreise.fNettoPreis
                FROM tartikel
                JOIN tartikelsonderpreis 
                    ON tartikelsonderpreis.kArtikel = tartikel.kArtikel
                JOIN tsonderpreise 
                    ON tsonderpreise.kArtikelSonderpreis = tartikelsonderpreis.kArtikelSonderpreis
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . '
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikelsonderpreis.kArtikel = tartikel.kArtikel
                    AND tsonderpreise.kKundengruppe = ' . $customerGroupID . "
                    AND tartikelsonderpreis.cAktiv = 'Y'
                    AND tartikelsonderpreis.dStart <= NOW()
                    AND (tartikelsonderpreis.dEnde IS NULL OR tartikelsonderpreis.dEnde >= CURDATE())
                    AND (tartikelsonderpreis.nAnzahl < tartikel.fLagerbestand OR tartikelsonderpreis.nIstAnzahl = 0)
                    " . self::getParentSQL() . '
                    ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($specialOffers, \min(\count($specialOffers), $limit));
    }

    /**
     * @param int $limit
     * @param int $customerGroupID
     * @return array
     * @former gibNeuImSortiment()
     * @since 5.0.0
     */
    public static function getNewProducts(int $limit, int $customerGroupID = 0): array
    {
        if (!$limit) {
            $limit = 20;
        }
        if (!$customerGroupID) {
            $customerGroupID = CustomerGroup::getDefaultGroupID();
        }
        $config = Shop::getSettings([\CONF_BOXEN]);
        $days   = ($config['boxen']['box_neuimsortiment_alter_tage'] > 0)
            ? (int)$config['boxen']['box_neuimsortiment_alter_tage']
            : 30;
        $new    = Shop::Container()->getDB()->query(
            'SELECT tartikel.kArtikel
                FROM tartikel
                LEFT JOIN tartikelsichtbarkeit 
                    ON tartikel.kArtikel = tartikelsichtbarkeit.kArtikel
                    AND tartikelsichtbarkeit.kKundengruppe = ' . $customerGroupID . "
                WHERE tartikelsichtbarkeit.kArtikel IS NULL
                    AND tartikel.cNeu = 'Y'
                    AND dErscheinungsdatum <= NOW()
                    AND DATE_SUB(NOW(), INTERVAL " . $days . ' DAY) < tartikel.dErstellt
                    ' . self::getParentSQL() . '
                    ' . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            ReturnType::ARRAY_OF_OBJECTS
        );

        return self::randomizeAndLimit($new, \min(\count($new), $limit));
    }
}
