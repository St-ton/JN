<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

class AnonymizeDeletedCustomer implements MethodInterface
{

    public function __construct()
    {
    }

    /**
     * Auto-anonymize personal data when customer accounts was deleted
     */
    public function execute()
    {
        $vQueries    = array();
        $vQueries[]  = "UPDATE `tbewertung` b
                    SET
                        b.`cName` = 'Anonym', `kKunde` = 0
                    WHERE
                        b.`kKunde` > 0
                        AND b.`kKunde` NOT IN (SELECT `kKunde` FROM `tkunde`)";

        $vQueries[]  = "UPDATE `tzahlungseingang`
                    SET
                        `cZahler` = '-'
                    WHERE
                        `cAbgeholt` != 'N'
                        AND `kBestellung` IN (
                            SELECT `kBestellung`
                            FROM `tbestellung` b
                            WHERE b.`kKunde` NOT IN (SELECT `kKunde` FROM `tkunde`)
                        )";

        $vQueries[]  = "UPDATE `tnewskommentar`
                    SET
                        `cName` = 'Anonym', `cEmail` = 'Anonym', `kKunde` = 0
                    WHERE
                        `kKunde` > 0
                        AND `kKunde` NOT IN (SELECT kKunde FROM tkunde)";

        foreach ($vQueries as $szQuery) {
            \Shop::Container()->getDB()->query($szQuery, \DB\ReturnType::AFFECTED_ROWS);
        }
    }

}
