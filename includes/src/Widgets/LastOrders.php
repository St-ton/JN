<?php declare(strict_types=1);

namespace JTL\Widgets;

use JTL\Router\Controller\Backend\OrderController;
use JTL\Shop;

/**
 * Class LastOrders
 * @package Plugin\jtl_widgets
 */
class LastOrders extends AbstractWidget
{
    /**
     * @inheritDoc
     * @throws \SmartyException
     */
    public function getContent(): string
    {
        if (\method_exists($this, 'setPermission')) {
            $this->setPermission('ORDER_VIEW');
        }
        $controller = new OrderController(
            $this->getDB(),
            Shop::Container()->getCache(),
            Shop::Container()->getAlertService(),
            Shop::Container()->getAdminAccount(),
            Shop::Container()->getGetText()
        );
        $orders     = $controller->getOrders(' LIMIT 10', '');

        return $this->getSmarty()
            ->assign('cDetail', 'tpl_inc/widgets/lastOrdersDetail.tpl')
            ->assign('orders', $orders)
            ->assign(
                'cDetailPosition',
                'tpl_inc/widgets/lastOrdersDetailPosition.tpl'
            )
            ->assign('cAdminmenuPfadURL', Shop::getURL(true) . '/'. PFAD_ADMIN .  '/')
            ->fetch('tpl_inc/widgets/widgetLastOrders.tpl');
    }
}
