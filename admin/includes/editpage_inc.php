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

    return $oPortlet_arr;
}