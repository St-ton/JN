<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
$oNice = Nice::getInstance();
if ($oNice->checkErweiterung(SHOP_ERWEITERUNG_AUSWAHLASSISTENT)) {
    /**
     * @deprecated since 4.05
     * @param string    $cKey
     * @param int       $kKey
     * @param int       $_lid
     * @param JTLSmarty $smarty
     * @param array     $_conf
     * @return bool
     */
    function starteAuswahlAssistent($cKey, $kKey, $_lid, $smarty, $_conf)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);

        return false;
    }

    /**
     * @deprecated since 4.05
     * @param int      $_catID
     * @param stdClass $_pf
     * @param stdClass $_fsql
     * @param stdClass $_sr
     * @param int      $_pc
     * @param int      $_lmt
     */
    function baueFilterSelectionWizard($_catID, &$_pf, &$_fsql, &$_sr, &$_pc, &$_lmt)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 4.05
     * @param array $_mmf
     * @param bool  $_bmmwv
     */
    function filterSelectionWizard($_mmf, &$_bmmwv)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 4.05
     * @param int    $_kmmw
     * @param int    $_qID
     * @param int    $_catID
     * @param bool   $_bfe
     * @param object $_sr
     * @param object $_pf
     * @param bool   $_bmmwv
     */
    function processSelectionWizard($_kmmw, $_qID, $_catID, &$_bfe, &$_sr, &$_pf, &$_bmmwv)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 4.05
     * @param int $_kmmw
     * @param int $_kawf
     * @param int $_qID
     * @param int $_catID
     */
    function setSelectionWizardAnswer($_kmmw, $_kawf, $_qID, $_catID)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 4.05
     * @param int $_qID
     * @param int $_catID
     */
    function resetSelectionWizard($_qID, $_catID)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);
    }

    /**
     * @deprecated since 4.05
     * @param string $_p
     * @return array
     */
    function extractAAURL($_p)
    {
        trigger_error(__FUNCTION__ . ' is deprecated and does no longer do anything.', E_USER_DEPRECATED);

        return [];
    }
}
