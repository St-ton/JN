<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * @return mixed
 */
function getPortlets()
{
    $oPortlet_arr = Shop::DB()->selectAll('teditorportlets', [], [], '*', 'cGroup');

    foreach ($oPortlet_arr as $i => $oPortlet) {
        $oPortlet_arr[$i]->cContent = '';
        $cClass                     = 'Portlet' . $oPortlet->cClass;
        $cClassFile                 = 'class.' . $cClass . '.php';
        $cClassPath                 = PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_PORTLETS . $cClassFile;
        $oPortlet->cTitle           = str_replace(['--', ' '], '-', $oPortlet->cTitle);

        // Plugin?
        $oPlugin = null;
        if (isset($oPortlet->kPlugin) && $oPortlet->kPlugin > 0) {
            $oPlugin    = new Plugin($oPortlet->kPlugin);
            $cClass     = 'Portlet' . $oPlugin->oPluginEditorPortletAssoc_arr[$oPortlet->kPortlet]->cClass;
            $cClassPath = $oPlugin->oPluginEditorPortletAssoc_arr[$oPortlet->kPortlet]->cClassAbs;
        }
        if (file_exists($cClassPath)) {
            require_once $cClassPath;
            if (class_exists($cClass)) {
                /** @var WidgetBase $oClassObj */
                $oClassObj                         = new $cClass(null, null, $oPlugin);
                $oPortlet_arr[$i]->cPreviewContent = $oClassObj->getPreviewContent();
            }
        }
    }


    /*$oPortlet_arr   = [];
    $oH1            = new stdClass();
    $oH1->title     = 'Heading 1';
    $oH1->content   = '<h1>Heading</h1>';
    $oPortlet_arr[] = $oH1;

    $oH2            = new stdClass();
    $oH2->title     = 'Heading 2';
    $oH2->content   = '<h2>Heading</h2>';
    $oPortlet_arr[] = $oH2;

    $oH3            = new stdClass();
    $oH3->title     = 'Heading 3';
    $oH3->content   = '<h3>Heading</h3>';
    $oPortlet_arr[] = $oH3;

    $oPa            = new stdClass();
    $oPa->title     = 'Paragraph';
    $oPa->content   = '<p>paragraph</p>';
    $oPortlet_arr[] = $oPa;*/

    return $oPortlet_arr;
}