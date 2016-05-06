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
        $cSuchSQL = new stdClass();
        $oUnlockRequest_arr = array();
        $kRequestCountTotal = 0;

        $cSuchSQL->cWhere = "";
        array_push ($oUnlockRequest_arr, gibBewertungFreischalten ($cSQL, $cSuchSQL));
        $kRequestCountTotal += count (end ($oUnlockRequest_arr));

        $cSuchSQL->cOrder = " dZuletztGesucht DESC ";
        array_push ($oUnlockRequest_arr, gibSuchanfrageFreischalten ($cSQL, $cSuchSQL));
        $kRequestCountTotal += count (end ($oUnlockRequest_arr));

        array_push ($oUnlockRequest_arr, gibTagFreischalten ($cSQL, $cSuchSQL));
        $kRequestCountTotal += count (end ($oUnlockRequest_arr));

        array_push ($oUnlockRequest_arr, gibNewskommentarFreischalten ($cSQL, $cSuchSQL));
        $kRequestCountTotal += count (end ($oUnlockRequest_arr));

        $cSuchSQL->cOrder = " tnewsletterempfaenger.dEingetragen DESC";
        array_push ($oUnlockRequest_arr, gibNewsletterEmpfaengerFreischalten ($cSQL, $cSuchSQL));
        $kRequestCountTotal += count (end ($oUnlockRequest_arr));

        $this->oSmarty->assign("oUnlockRequest_arr", $oUnlockRequest_arr);
        $this->oSmarty->assign("oUnlockRequestGroups_arr", [
            "Bewertungen",
            "Suchanfragen",
            "Tags",
            "Newskommentare",
            "Newsletterempf&auml;nger"
        ]);
        $this->oSmarty->assign("kRequestCountTotal", $kRequestCountTotal);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->oSmarty->fetch(dirname(__FILE__) . '/widgetUnlockRequestNotifier.tpl');
    }
}
