<?php
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Bestellung.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'bestellungen_inc.php';

/**
 * Class WidgetLastOrders_jtl_widgets
 */
class WidgetLastOrders_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $oBestellung_arr = gibBestellungsUebersicht(' LIMIT 10', '');
        $this->oSmarty->assign('oBestellung_arr', $oBestellung_arr);
    }

    /**
     * @return bool|mixed|string
     */
    public function getContent()
    {
        $this->oSmarty->assign('cDetail', "{$this->oPlugin->cAdminmenuPfad}/widget/lastOrdersDetail.tpl");
        $this->oSmarty->assign('cDetailPosition', "{$this->oPlugin->cAdminmenuPfad}/widget/lastOrdersDetailPosition.tpl");
        $this->oSmarty->assign('cAdminmenuPfadURL', $this->oPlugin->cAdminmenuPfadURLSSL);

        return $this->oSmarty->fetch(dirname(__FILE__) . '/widgetLastOrders.tpl');
    }
}
