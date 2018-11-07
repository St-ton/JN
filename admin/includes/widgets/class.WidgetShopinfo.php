<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetShopinfo
 */
class WidgetShopinfo extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $oTpl            = Template::getInstance();
        $strTplVersion   = $oTpl->getVersion();
        $strFileVersion  = Shop::getApplicationVersion();
        $strDBVersion    = Shop::getShopDatabaseVersion();
        $strUpdated      = date_format(date_create(getJTLVersionDB(true)), 'd.m.Y, H:i:m');
        $strMinorVersion = APPLICATION_BUILD_SHA === '#DEV#' ? 'DEV' : '';

        $this->oSmarty->assign('strFileVersion', $strFileVersion)
            ->assign('strDBVersion', $strDBVersion)
            ->assign('strTplVersion', $strTplVersion)
            ->assign('strUpdated', $strUpdated)
            ->assign('strMinorVersion', $strMinorVersion)
            ->assign('JTLURL_GET_SHOPVERSION', JTLURL_GET_SHOPVERSION);
    }

    /**
     * @return string
     * @throws SmartyException
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }
}
