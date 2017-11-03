<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . 'class.PortletBase.php';

/**
 * @param int $kPortlet
 * @return PortletBase
 * @throws Exception
 */
function createPortlet($kPortlet)
{
    $oDbPortlet = Shop::DB()->select('tcmsportlet', 'kPortlet', $kPortlet);

    if (!is_object($oDbPortlet)) {
        throw new Exception('Portlet ID could not be found in the database.');
    }

    if (isset($oDbPortlet->kPlugin) && $oDbPortlet->kPlugin > 0) {
        $oPlugin    = new Plugin($oDbPortlet->kPlugin);
        $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClass;
        $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$kPortlet]->cClassAbs;
    } else {
        $cClass     = 'Portlet' . $oDbPortlet->cClass;
        $cClassFile = 'class.' . $cClass . '.php';
        $cClassPath = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . $cClassFile;
    }

    require_once $cClassPath;

    return new $cClass($kPortlet);
}

/**
 * @return PortletBase[]
 */
function getPortlets()
{
    $oDbPortlet_arr = Shop::DB()->selectAll('tcmsportlet', [], []);
    $oPortlet_arr   = [];

    foreach ($oDbPortlet_arr as $i => $oDbPortlet) {
        $oPortlet_arr[] = createPortlet($oDbPortlet->kPortlet);
    }

    return $oPortlet_arr;
}

/**
 * @param int $kPortlet
 * @param array $properties
 * @return string
 */
function getPortletPreviewHtml($kPortlet, $properties)
{
    return createPortlet($kPortlet)
        ->setProperties($properties)
        ->getPreviewHtml();
}

/**
 * @param int $kPortlet
 * @param array $properties
 * @return string
 */
function getPortletConfigPanelHtml($kPortlet, $properties)
{
    return createPortlet($kPortlet)
        ->setProperties($properties)
        ->getConfigPanelHtml();
}

/**
 * @param $kPortlet
 * @return array[]
 */
function getPortletDefaultProps($kPortlet)
{
    return createPortlet($kPortlet)
        ->getDefaultProps();
}

/**
 * @param string $cKey
 * @param int $kKey
 * @param int $kSprache
 * @param array|object $oCmsPageData - object tree to be json encoded and saved in the DB
 */
function saveCmsPage($cKey, $kKey, $kSprache, $oCmsPageData)
{
    $oCmsPage = Shop::DB()->select('tcmspage', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache]);

    if ($oCmsPage === null) {
        $oCmsPage = (object)[
            'cKey' => $cKey,
            'kKey' => $kKey,
            'kSprache' => $kSprache,
            'cJson' => json_encode($oCmsPageData),
        ];
        $oCmsPage->kPage = Shop::DB()->insert('tcmspage', $oCmsPage);
    } else {
        $oCmsPage->cJson = json_encode($oCmsPageData);
        Shop::DB()->update('tcmspage', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache], $oCmsPage);
    }


    foreach ($oCmsPageData as $areaId => $areaPortlets) {
        $cHtml = '';

        foreach ($areaPortlets as $portlet) {
            $cHtml .= createPortlet($portlet['portletId'])
                ->setProperties($portlet['properties'])
                ->setSubAreas($portlet['subAreas'])
                ->getFinalHtml();
        }

        $oCmsPageContent = Shop::DB()->select(
            'tcmspagecontent', ['kPage', 'cAreaId'], [$oCmsPage->kPage, $areaId]
        );

        if ($oCmsPageContent === null) {
            $oCmsPageContent = (object)[
                'kPage' => $oCmsPage->kEditorPage,
                'cAreaId' => $areaId,
                'cHtml' => $cHtml,
            ];
            $oCmsPageContent->kEditorPageContent = Shop::DB()->insert('teditorpagecontent', $oCmsPageContent);
        } else {
            $oCmsPageContent->cContent = $cHtml;
            Shop::DB()->update('tcmspagecontent', 'kPageContent', $oCmsPageContent->kPageContent, $oCmsPageContent);
        }
    }
}

/**
 * @param int $cKey
 * @param int $kKey
 * @param int $kSprache
 * @return object
 */
function loadCmsPage($cKey, $kKey, $kSprache)
{
    $oCmsPage = Shop::DB()->select('tcmspage', ['cKey', 'kKey', 'kSprache'], [$cKey, $kKey, $kSprache]);

    if ($oCmsPage === null) {
        return (object)[];
    }

    return $oCmsPage->cJson;
}
