<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Helpers\Text;
use JTL\Shop;

/**
 * Class Serverinfo
 * @package JTL\Widgets
 */
class Serverinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $cUrl = \parse_url(Shop::getURL());
        $this->oSmarty->assign('phpOS', \PHP_OS)
                      ->assign('phpVersion', Text::htmlentities(\PHP_VERSION))
                      ->assign('serverAddress', Text::htmlentities($_SERVER['SERVER_ADDR']))
                      ->assign('serverHTTPHost', Text::htmlentities($_SERVER['HTTP_HOST']))
                      ->assign('mySQLVersion', Text::htmlentities($this->oDB->getServerInfo()))
                      ->assign('mySQLStats', Text::htmlentities($this->oDB->getServerStats()))
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
