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
        $oVisitors_arr = Shop::DB()->query("
            SELECT
                ANY_VALUE(`otab`.`kBesucher`) AS kBesucher,
                ANY_VALUE(`otab`.`cIP`) AS cIP,
                ANY_VALUE(`otab`.`cSessID`) AS cSessID,
                ANY_VALUE(`otab`.`cID`) AS cID,
                ANY_VALUE(`otab`.`kKunde`) AS kKunde,
                ANY_VALUE(`otab`.`kBestellung`) AS kBestellung,
                ANY_VALUE(`otab`.`cReferer`) AS cReferer,
                ANY_VALUE(`otab`.`cUserAgent`) AS cUserAgent,
                ANY_VALUE(`otab`.`cEinstiegsseite`) AS cEinstiegsseite,
                ANY_VALUE(`otab`.`cBrowser`) AS cBrowser,
                ANY_VALUE(`otab`.`cAusstiegsseite`) AS cAusstiegsseite,
                ANY_VALUE(`otab`.`kBesucherBot`) AS kBesucherBot,
                ANY_VALUE(`dLetzteAktivitaet`) AS dLetzteAktivitaet,
                ANY_VALUE(`otab`.`dZeit`) AS dZeit,
                ANY_VALUE(`otab`.`fGesamtsumme`) AS fGesamtsumme,
                ANY_VALUE(`otab`.`cVorname`) AS cVorname,
                ANY_VALUE(`otab`.`cNachname`) AS cNachname,
                ANY_VALUE(`otab`.`dErstellt`) AS dErstellt,
                ANY_VALUE(`otab`.`cNewsletter`) AS cNewsletter
            FROM
                (SELECT
                    `tbesucher`.*,
                    `tbestellung`.`fGesamtsumme`,
                    `tkunde`.`cVorname`,
                    `tkunde`.`cNachname`,
                    `tkunde`.`dErstellt`,
                    `tkunde`.`cNewsletter`
                FROM
                    `tbesucher`
                        LEFT JOIN `tbestellung` ON `tbesucher`.`kBestellung` = `tbestellung`.`kBestellung`
                        LEFT JOIN `tkunde` ON `tbesucher`.`kKunde` = `tkunde`.`kKunde`
                WHERE
                    `tbesucher`.`kBesucherBot` = 0
                    AND `tbesucher`.`dLetzteAktivitaet` = (SELECT max(`dLetzteAktivitaet`) FROM `tbesucher`)
                ) AS `otab`
            GROUP BY
                  `otab`.`kKunde`
                /*, cSessID*/   /* grouping all the same customers together, to see different browser-logins as one */
                HAVING `otab`.`kKunde` != 0
            UNION
            SELECT * FROM
                (SELECT
                    `tbesucher`.*,
                    `tbestellung`.`fGesamtsumme` as fGesamtsumme,
                    `tkunde`.`cVorname` as cVorname,
                    `tkunde`.`cNachname` as cNachname,
                    `tkunde`.`dErstellt` as dErstellt,
                    `tkunde`.`cNewsletter` as cNewsletter
                FROM
                    `tbesucher`
                        LEFT JOIN `tbestellung` ON `tbesucher`.`kBestellung` = `tbestellung`.`kBestellung`
                        LEFT JOIN `tkunde` ON `tbesucher`.`kKunde` = `tkunde`.`kKunde`
                WHERE
                    `tbesucher`.`kBesucherBot` = 0
                    AND `tbesucher`.`kKunde` = 0
                ORDER BY
                    `tbesucher`.`dLetzteAktivitaet`
            ) AS `otab`
            ;
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
