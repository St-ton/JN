<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetMarketplace
 */
class WidgetMarketplace extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $this->oSmarty->assign('nVersionDB', getJTLVersionDB());
        $this->oSmarty->assign('JTLURL_GET_SHOPMARKETPLACE', JTLURL_GET_SHOPMARKETPLACE);
        $this->assignPluginCheck();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/marketplace.tpl');
    }

    /**
     *
     */
    public function assignPluginCheck()
    {
        $cPluginCheck_arr = [];
        $oRes             = Shop::Container()->getDB()->query("SELECT cPluginID, nVersion FROM tplugin", 2);
        if (isset($oRes) && count($oRes) > 0) {
            foreach ($oRes as $oPlugin) {
                if (isset($oPlugin->cPluginID, $oPlugin->nVersion) &&
                    strlen($oPlugin->cPluginID) > 0 &&
                    is_numeric($oPlugin->nVersion)
                ) {
                    $cPluginCheck_arr[] = "{$oPlugin->cPluginID},{$oPlugin->nVersion}";
                }
            }
        }
        $this->oSmarty->assign('cPluginCheck', implode('|', $cPluginCheck_arr));
    }
}
