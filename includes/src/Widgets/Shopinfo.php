<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Shop;

/**
 * Class Shopinfo
 * @package JTL\Widgets
 */
class Shopinfo extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        $strTplVersion   = Shop::Container()->getTemplateService()->getActiveTemplate()->getVersion();
        $strFileVersion  = \APPLICATION_VERSION;
        $strDBVersion    = Shop::getShopDatabaseVersion();
        $strUpdated      = \date_format(\date_create($this->getLastMigrationDate()), 'd.m.Y, H:i:m');
        $strMinorVersion = \APPLICATION_BUILD_SHA === '#DEV#' ? 'DEV' : '';

        $this->oSmarty->assign('strFileVersion', $strFileVersion)
            ->assign('strDBVersion', $strDBVersion)
            ->assign('strTplVersion', $strTplVersion)
            ->assign('strUpdated', $strUpdated)
            ->assign('strMinorVersion', $strMinorVersion);

        $this->setPermission('DIAGNOSTIC_VIEW');
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getContent()
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }

    /**
     * @return string
     */
    private function getLastMigrationDate(): string
    {
        $latestUpdate = $this->getDB()->getSingleObject('SELECT MAX(dExecuted) AS date FROM tmigration');

        return $latestUpdate->date ?? '';
    }
}
