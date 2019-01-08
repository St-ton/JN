<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

use DB\ReturnType;

/**
 * Class AnonymizeDeletedCustomer
 * @package GeneralDataProtection
 */
class AnonymizeDeletedCustomer extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute(): void
    {
        $this->anonymizeRatings();
        $this->anonymizeReceivedPayments();
        $this->anonymizeNewsComments();
    }

    /**
     * anonymize orphaned ratings.
     * (e.g. of canceled memberships)
     */
    private function anonymizeRatings(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            "UPDATE tbewertung b
            SET
                b.cName  = 'Anonym',
                b.kKunde = 0
            WHERE
                b.cName != 'Anonym'
                AND b.kKunde > 0
                AND dDatum <= :pDateLimit
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = b.kKunde)
            LIMIT :pLimit",
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * anonymize received payments.
     * (replace `cZahler`(e-mail) in `tzahlungseingang`)
     */
    private function anonymizeReceivedPayments(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            "UPDATE tzahlungseingang z
            SET
                z.cZahler = '-'
            WHERE
                z.cZahler != '-'
                AND z.cAbgeholt != 'N'
                AND NOT EXISTS (
                    SELECT k.kKunde
                    FROM tkunde k INNER JOIN tbestellung b ON k.kKunde = b.kKunde
                    WHERE b.kBestellung = z.kBestellung
                )
                AND z.dZeit <= :pDateLimit
            ORDER BY z.dZeit ASC
            LIMIT :pLimit",
            [
                'pDateLimit' => $this->dateLimit,
                'pLimit'     => $this->workLimit
            ],
            ReturnType::DEFAULT
        );
    }

    /**
     * anonymize comments of news without registered customers
     * (delete names and e-mails from `tnewskommentar` and remove the customer-relation)
     *
     * CONSIDER: using no time base or limit!
     */
    private function anonymizeNewsComments(): void
    {
        \Shop::Container()->getDB()->queryPrepared(
            "UPDATE tnewskommentar n
            SET
                n.cName = 'Anonym',
                n.cEmail = 'Anonym',
                n.kKunde = 0
            WHERE
                n.cName != 'Anonym'
                AND n.cEmail != 'Anonym'
                AND n.kKunde > 0
                AND NOT EXISTS (SELECT kKunde FROM tkunde WHERE tkunde.kKunde = n.kKunde)
            LIMIT :pLimit",
            ['pLimit' => $this->workLimit],
            ReturnType::DEFAULT
        );
    }
}
