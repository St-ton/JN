<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class Shopinfo
 *
 * @package Widgets
 */
class Shopinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $tpl             = \Template::getInstance();
        $strTplVersion   = $tpl->getVersion();
        $strFileVersion  = \Shop::getApplicationVersion();
        $strDBVersion    = \Shop::getShopDatabaseVersion();
        $strUpdated      = \date_format(\date_create(\getJTLVersionDB(true)), 'd.m.Y, H:i:m');
        $strMinorVersion = \APPLICATION_BUILD_SHA === '#DEV#' ? 'DEV' : '';

        $this->oSmarty->assign('strFileVersion', $strFileVersion)
                      ->assign('strDBVersion', $strDBVersion)
                      ->assign('strTplVersion', $strTplVersion)
                      ->assign('strUpdated', $strUpdated)
                      ->assign('strMinorVersion', $strMinorVersion);
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }
}
