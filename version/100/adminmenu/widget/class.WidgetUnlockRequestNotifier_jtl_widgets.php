<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'freischalten_inc.php';

/**
 * Class WidgetUnlockRequestNotifier_jtl_widgets
 */
class WidgetUnlockRequestNotifier_jtl_widgets extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        $cSQL = " LIMIT 5";
        $cBewertungSQL = new stdClass();
        $cBewertungSQL->cWhere = "";

        $this->oSmarty->assign("oBewertung_arr", gibBewertungFreischalten ($cSQL, $cBewertungSQL));
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(dirname(__FILE__) . '/widgetUnlockRequestNotifier.tpl');
    }
}
