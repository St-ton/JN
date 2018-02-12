<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . PFAD_WIDGETS . 'class.WidgetBase.php';

/**
 * Class WidgetVisitorsOnline
 */
class WidgetVisitorsOnline extends WidgetBase
{
    /**
     *
     */
    public function init()
    {
        archiviereBesucher();
    }

    /**
     * @return array
     */
    public function getVisitors()
    {
        // clause 'ANY_VALUE' is needed by servers, who has the 'sql_mode'-setting 'only_full_group_by' enabled.
        // this is the default since mysql version >= 5.7.x
        $oVisitors_arr = Shop::DB()->query(
            "SELECT
                `otab`.*,
                `tbestellung`.`fGesamtsumme` AS fGesamtsumme, `tkunde`.`cVorname` as cVorname,
                `tkunde`.`cNachname` AS cNachname, `tkunde`.`dErstellt` as dErstellt,
                `tkunde`.`cNewsletter` AS cNewsletter
            FROM
                (SELECT
                    ANY_VALUE(`tbesucher`.`kKunde`) AS kKunde,
                    max(`tbesucher`.`dLetzteAktivitaet`) AS dLetzteAktivitaet
                FROM
                    `tbesucher`
                WHERE
                    `tbesucher`.`kBesucherBot` = 0
                GROUP BY
                    `tbesucher`.`kKunde`
                    HAVING `tbesucher`.`kKunde` != 0   -- ignore all guests for now (unite them later)
                ) AS `itab`
                INNER JOIN `tbesucher` AS `otab` ON `itab`.`kKunde` = `otab`.`kKunde` AND `itab`.`dLetzteAktivitaet` = `otab`.`dLetzteAktivitaet`
                LEFT JOIN `tbestellung` ON `otab`.`kBestellung` = `tbestellung`.`kBestellung`
                LEFT JOIN `tkunde` ON `otab`.`kKunde` = `tkunde`.`kKunde`
            UNION
            SELECT
                `tbesucher`.*,
                `tbestellung`.`fGesamtsumme` as fGesamtsumme, `tkunde`.`cVorname` as cVorname,
                `tkunde`.`cNachname` as cNachname, `tkunde`.`dErstellt` as dErstellt,
                `tkunde`.`cNewsletter` as cNewsletter
            FROM
                `tbesucher`
                    LEFT JOIN `tbestellung` ON `tbesucher`.`kBestellung` = `tbestellung`.`kBestellung`
                    LEFT JOIN `tkunde` ON `tbesucher`.`kKunde` = `tkunde`.`kKunde`
            WHERE
                `tbesucher`.`kBesucherBot` = 0
                AND `tbesucher`.`kKunde` = 0   -- only guests are of interest here
        ", 2);
        if (is_array($oVisitors_arr)) {
            foreach ($oVisitors_arr as $i => $oVisitor) {
                $oVisitors_arr[$i]->cNachname = trim(entschluesselXTEA($oVisitor->cNachname));
                if ($oVisitor->kBestellung > 0) {
                    $oVisitors_arr[$i]->fGesamtsumme = gibPreisStringLocalized($oVisitor->fGesamtsumme);
                }
            }
        } else {
            $oVisitors_arr = [];
        }

        return $oVisitors_arr;
    }

    /**
     * @param array $oVisitors_arr
     * @return stdClass
     */
    public function getVisitorsInfo($oVisitors_arr)
    {
        $oInfo            = new stdClass();
        $oInfo->nCustomer = 0;
        $oInfo->nAll      = count($oVisitors_arr);
        if ($oInfo->nAll > 0) {
            foreach ($oVisitors_arr as $i => $oVisitor) {
                if ($oVisitor->kKunde > 0) {
                    $oInfo->nCustomer++;
                }
            }
        }
        $oInfo->nUnknown = $oInfo->nAll - $oInfo->nCustomer;

        return $oInfo;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $oVisitors_arr = $this->getVisitors();
        return $this->oSmarty->assign('oVisitors_arr', $oVisitors_arr)
            ->assign('oVisitorsInfo', $this->getVisitorsInfo($oVisitors_arr))
            ->fetch('tpl_inc/widgets/visitors_online.tpl');
    }
}
