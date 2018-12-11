<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class Serverinfo
 *
 * @package Widgets
 */
class Serverinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $cUrl = \parse_url(\Shop::getURL());
        $this->oSmarty->assign('phpOS', \PHP_OS)
                      ->assign('phpVersion', \StringHandler::htmlentities(\PHP_VERSION))
                      ->assign('serverAddress', \StringHandler::htmlentities($_SERVER['SERVER_ADDR']))
                      ->assign('serverHTTPHost', \StringHandler::htmlentities($_SERVER['HTTP_HOST']))
                      ->assign('mySQLVersion', \StringHandler::htmlentities($this->oDB->getServerInfo()))
                      ->assign('mySQLStats', \StringHandler::htmlentities($this->oDB->getServerStats()))
                      ->assign('cShopHost', $cUrl['scheme'] . '://' . $cUrl['host']);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serverinfo.tpl');
    }
}
