<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Widgets;

/**
 * Class VisitorsOnline
 *
 * @package Widgets
 */
class VisitorsOnline extends AbstractWidget
{
    /**
     *
     */
    public function init()
    {
        \Visitor::archive();
    }

    /**
     * @return array
     */
    public function getVisitors(): array
    {
        // clause 'ANY_VALUE' is needed by servers, who has the 'sql_mode'-setting 'only_full_group_by' enabled.
        // this is the default since mysql version >= 5.7.x
        $visitors      = $this->oDB->query(
            'SELECT `otab`.*,
                `tbestellung`.`fGesamtsumme` AS fGesamtsumme, `tbestellung`.`dErstellt` as dErstellt,
                `tkunde`.`cVorname` as cVorname, `tkunde`.`cNachname` AS cNachname,
                `tkunde`.`cNewsletter` AS cNewsletter
            FROM `tbesucher` AS `otab`
                INNER JOIN `tkunde` ON `otab`.`kKunde` = `tkunde`.`kKunde`
                LEFT JOIN `tbestellung` ON `otab`.`kBestellung` = `tbestellung`.`kBestellung`
            WHERE `otab`.`kKunde` != 0
                AND `otab`.`kBesucherBot` = 0
                AND `otab`.`dLetzteAktivitaet` = (
                    SELECT MAX(`tbesucher`.`dLetzteAktivitaet`)
                    FROM `tbesucher`
                    WHERE `tbesucher`.`kKunde` = `otab`.`kKunde`
                )
            UNION
            SELECT
                `tbesucher`.*,
                `tbestellung`.`fGesamtsumme` AS fGesamtsumme, `tbestellung`.`dErstellt` as dErstellt,
                `tkunde`.`cVorname` AS cVorname, `tkunde`.`cNachname` AS cNachname,
                `tkunde`.`cNewsletter` AS cNewsletter
            FROM `tbesucher`
                LEFT JOIN `tbestellung` 
                    ON `tbesucher`.`kBestellung` = `tbestellung`.`kBestellung`
                LEFT JOIN `tkunde` 
                    ON `tbesucher`.`kKunde` = `tkunde`.`kKunde`
            WHERE `tbesucher`.`kBesucherBot` = 0
                AND `tbesucher`.`kKunde` = 0',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $cryptoService = \Shop::Container()->getCryptoService();
        foreach ($visitors as $visitor) {
            $visitor->cNachname = trim($cryptoService->decryptXTEA($visitor->cNachname ?? ''));
            if ($visitor->kBestellung > 0) {
                $visitor->fGesamtsumme = \Preise::getLocalizedPriceString($visitor->fGesamtsumme);
            }
        }

        return $visitors;
    }

    /**
     * @param array $visitors
     * @return \stdClass
     */
    public function getVisitorsInfo(array $visitors): \stdClass
    {
        $info            = new \stdClass();
        $info->nCustomer = 0;
        $info->nAll      = count($visitors);
        if ($info->nAll > 0) {
            foreach ($visitors as $i => $oVisitor) {
                if ($oVisitor->kKunde > 0) {
                    $info->nCustomer++;
                }
            }
        }
        $info->nUnknown = $info->nAll - $info->nCustomer;

        return $info;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $visitors = $this->getVisitors();

        return $this->oSmarty->assign('oVisitors_arr', $visitors)
                             ->assign('oVisitorsInfo', $this->getVisitorsInfo($visitors))
                             ->fetch('tpl_inc/widgets/visitors_online.tpl');
    }
}
