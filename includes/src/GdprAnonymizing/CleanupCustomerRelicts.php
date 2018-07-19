<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class CleanupCustomerRelicts implements MethodInterface
{

    public function __construct()
    {
        //
    }

    /**
     * Delete customer relicts in logs and subtables and
     * delete shipping and billing-addresses of deleted customers
     */
    public function execute()
    {
        $vQueries   = array();
        $vQueries[] = "DELETE FROM tbesucher WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tbesucherarchiv WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tkundenattribut WHERE kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tkundenkontodaten WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tkundenwerbenkunden WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tkundenwerbenkundenbonus WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE FROM tzahlungsinfo WHERE kKunde > 0 AND kKunde NOT IN (SELECT kKunde FROM tkunde)";

        $vQueries[] = "DELETE k
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE b.cStatus IN (4, -1)
                AND b.cAbgeholt = 'Y'
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)";
        $vQueries[] = "DELETE k
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE b.cStatus IN (4, -1)
                AND b.cAbgeholt = 'Y'
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)";

        foreach ($vQueries as $szQuery) {
            \Shop::Container()->getDB()->query($szQuery, \DB\ReturnType::AFFECTED_ROWS);
        }
    }

}
