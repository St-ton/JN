<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Shop;
use JTL\Update\DBMigrationHelper;

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
        $parsed       = \parse_url(Shop::getURL());
        $mySQLVersion = DBMigrationHelper::getMySQLVersion()->innodb->version;
        $this->oSmarty->assign('phpOS', \PHP_OS)
            ->assign('phpVersion', \PHP_VERSION)
            ->assign('serverAddress', $_SERVER['SERVER_ADDR'] ?? '?')
            ->assign('serverHTTPHost', $_SERVER['HTTP_HOST'] ?? '?')
            ->assign('mySQLVersion', $this->oDB->getServerInfo())
            ->assign('mySQLStats', $this->oDB->getServerStats())
            ->assign('hasMySQLMinVersion', ($mySQLVersion === '') ? true : \version_compare(
                $mySQLVersion,
                \MYSQL_MIN_VERSION,
                '>='
            ))
            ->assign('cShopHost', $parsed['scheme'] . '://' . $parsed['host']);

        $this->setPermission('DIAGNOSTIC_VIEW');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/serverinfo.tpl');
    }
}
