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
    public function init(): void
    {
        $this->oSmarty->assign('strFileVersion', \APPLICATION_VERSION)
            ->assign('strDBVersion', Shop::getShopDatabaseVersion())
            ->assign('strTplVersion', Shop::Container()->getTemplateService()->getActiveTemplate()->getVersion())
            ->assign('strUpdated', \date_format(\date_create($this->getLastMigrationDate()), 'd.m.Y, H:i:m'))
            ->assign('strMinorVersion', \APPLICATION_BUILD_SHA === '#DEV#' ? 'DEV' : '');

        $this->setPermission('DIAGNOSTIC_VIEW');
    }

    /**
     * @return string
     * @throws \SmartyException
     */
    public function getContent(): string
    {
        return $this->oSmarty->fetch('tpl_inc/widgets/shopinfo.tpl');
    }

    /**
     * @return string
     */
    private function getLastMigrationDate(): string
    {
        return $this->getDB()->getSingleObject('SELECT MAX(dExecuted) AS date FROM tmigration')->date ?? '';
    }
}
