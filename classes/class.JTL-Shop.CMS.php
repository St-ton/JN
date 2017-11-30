<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class CMS
 */
class CMS
{
    public static $oPageParameters = null;

    public static function getCMSPage($cKey, $kKey, $kSprache)
    {
        $oCMSPageDB = Shop::DB()->select(
            'tcmspage',
            ['cKey', 'kKey', 'kSprache'],
            [$cKey, $kKey, $kSprache],
            null,
            null,
            null,
            null,
            false,
            'kPage'
        );

        if ($oCMSPageDB === null) {
            return null;
        }

        return new CMSPage($oCMSPageDB->kPage);
    }

    public static function getCurrentCMSPage()
    {
        $pageParams = self::getPageParameters();

        return self::getCMSPage($pageParams->cKey, $pageParams->kKey, $pageParams->kSprache);
    }

    public static function getPageParameters()
    {
        if (self::$oPageParameters === null) {
            self::$oPageParameters = (object)[ 'cKey' => '', 'kKey' => 0, 'kSprache' => Shop::getLanguage() ];
            $shopParams            = Shop::getParameters();
            $possibleKeys          = [
                'kArtikel', 'kHersteller', 'kKategorie', 'kLink', 'kMerkmalWert', 'kNews', 'kNewsKategorie',
                'kNewsUebersicht', 'kSuchanfrage', 'kTag', 'kUmfrage', 'suchspecial'
            ];

            foreach ($shopParams as $cKey => $kKey) {
                if (!empty($kKey) && in_array($cKey, $possibleKeys, true)) {
                    self::$oPageParameters->cKey = $cKey;
                    self::$oPageParameters->kKey = $kKey;
                    break;
                }
            }
        }

        return self::$oPageParameters;
    }

    public static function saveCmsPage($cKey, $kKey, $kSprache, $oCmsPageData)
    {
        $oCmsPage = new CMSPage();
        $oCmsPage->cKey = $cKey;
        $oCmsPage->kKey = $kKey;
        $oCmsPage->kSprache = $kSprache;
        $oCmsPage->data = $oCmsPageData;
        $oCmsPage->save();
    }

    /**
     * @param $cKey
     * @param $kKey
     * @param $kSprache
     * @return mixed|object
     */
    public static function getCmsPageJson($cKey, $kKey, $kSprache)
    {
        $oCmsPage = self::getCMSPage($cKey, $kKey, $kSprache);

        if ($oCmsPage === null) {
            return [];
        }

        return $oCmsPage->data;
    }

    /**
     * @param int $kPortlet
     * @return CMSPortlet
     * @throws Exception
     */
    public static function createPortlet($kPortlet)
    {
        $oDbPortlet = Shop::DB()->select('tcmsportlet', 'kPortlet', $kPortlet);

        if (!is_object($oDbPortlet)) {
            throw new Exception("Portlet ID $kPortlet could not be found in the database.", 404);
        }

        if (isset($oDbPortlet->kPlugin) && $oDbPortlet->kPlugin > 0) {
            $oPlugin    = new Plugin($oDbPortlet->kPlugin);
            $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClass;
            $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClassAbs;
            require_once $cClassPath;
        } else {
            $cClass     = 'Portlet' . $oDbPortlet->cClass;
        }

        return new $cClass($kPortlet);
    }

    /**
     * @return CMSPortlet[]
     */
    public static function getPortlets()
    {
        $oDbPortlet_arr = Shop::DB()->selectAll('tcmsportlet', [], []);
        $oPortlet_arr   = [];

        foreach ($oDbPortlet_arr as $i => $oDbPortlet) {
            $oPortlet_arr[] = self::createPortlet($oDbPortlet->kPortlet);
        }

        return $oPortlet_arr;
    }

    /**
     * @param int $kPortlet
     * @param array $properties
     * @return string
     */
    public static function getPortletPreviewHtml($kPortlet, $properties)
    {
        return self::createPortlet($kPortlet)
            ->setProperties($properties)
            ->getPreviewHtml();
    }

    /**
     * @param int $kPortlet
     * @param array $properties
     * @return string
     */
    public static function getPortletConfigPanelHtml($kPortlet, $properties)
    {
        return self::createPortlet($kPortlet)
            ->setProperties($properties)
            ->getConfigPanelHtml();
    }

    /**
     * @param $kPortlet
     * @return array
     */
    public static function getPortletDefaultProps($kPortlet)
    {
        return self::createPortlet($kPortlet)
            ->getDefaultProps();
    }

}
