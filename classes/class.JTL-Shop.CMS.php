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
    public static $cPageIdHash = '';

    public static function getCmsPage($cPageIdHash)
    {
        $oCMSPageDB = Shop::DB()->select('tcmspage', 'cIdHash', $cPageIdHash, null, null, null, null, false, 'kPage');

        if ($oCMSPageDB === null) {
            return null;
        }

        return new CMSPage($oCMSPageDB->kPage);
    }

    public static function getCurrentCmsPage()
    {

        return self::getCmsPage(self::getCurrentPageIdHash());
    }

    public static function getCurrentPageIdHash()
    {
        if (self::$cPageIdHash === '') {
            self::$cPageIdHash = md5(serialize(Shop::getParameters()));
        }

        return self::$cPageIdHash;
    }

    public static function saveCmsPage($cIdHash, $oCmsPageData)
    {
        $oCmsPage          = new CMSPage();
        $oCmsPage->cIdHash = $cIdHash;
        $oCmsPage->data    = $oCmsPageData;
        $oCmsPage->save();
    }

    public static function storeTemplate($cTemplateName, $cTemplateData)
    {
        $oCmsTemplate        = new CMSTemplate();
        $oCmsTemplate->cName = $cTemplateName;
        $oCmsTemplate->data  = $cTemplateData;
        $oCmsTemplate->save();
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
     * @return CMSTemplate[]
     */
    public static function getTemplates()
    {
        return Shop::DB()->selectAll('tcmstemplate', [], []);
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
