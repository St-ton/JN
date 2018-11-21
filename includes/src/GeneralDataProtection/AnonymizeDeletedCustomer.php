<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * anonymize personal data when customer accounts was deleted
 *
 * names of the tables, we manipulate:
 *
 * `tbewertung`
 * `tzahlungseingang`
 * `tnewskommentar`
 */
class AnonymizeDeletedCustomer extends Method implements MethodInterface
{
    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->anon_tbewertung();
        $this->anon_tzahlungseingang();
        $this->anon_tnewskommentar();
    }

    /**
     * anonymize orphaned ratings.
     * (e.g. of canceled memberships)
     */
    private function anon_tbewertung()
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
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * anonymize received payments.
     * (replace `cZahler`(e-mail) in `tzahlungseingang`)
     */
    private function anon_tzahlungseingang()
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
                'pDateLimit' => $this->szDateLimit,
                'pLimit'     => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * anonymize comments of news, where no (more) registered customers are there for these.
     * (delete names and e-mails from `tnewskommentar` and remove the customer-relation)
     *
     * CONSIDERE: using no time-base or limit!
     */
    private function anon_tnewskommentar()
    {
        $vResult = \Shop::Container()->getDB()->queryPrepared(
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
            [
                'pLimit' => $this->iWorkLimit
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}
