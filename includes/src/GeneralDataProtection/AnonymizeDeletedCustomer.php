<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
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
     * @var string
     */
    protected $szReasonName;

    /**
     * AnonymizeDeletedCustomer constructor
     *
     * @param $oNow
     * @param $iInterval
     */
    public function __construct($oNow, $iInterval)
    {
        parent::__construct($oNow, $iInterval);
        $this->szReasonName = substr(__CLASS__, strrpos(__CLASS__, '\\')) . ': ';
    }

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
        $this->szReason = $this->szReasonName . 'anonymize orphanded ratings';
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tbewertung b
            WHERE
                b.cName != "Anonym"
                AND b.kKunde > 0
                AND b.kKunde NOT IN (SELECT kKunde FROM tkunde)
                AND DATE(dDatum) < DATE_SUB(DATE(:pNow), INTERVAL :pInterval DAY)
                LIMIT :pLimit',
            [
                'pInterval' => $this->iInterval,
                'pNow'      => $this->oNow->format('Y-m-d H:i:s'),
                'pLimit'    => $this->iWorkLimit
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('UPDATE tbewertung b
                SET
                    b.cName = "Anonym",
                    kKunde = 0
                WHERE
                    kBewertung = :pKeyBewertung',
                ['pKeyBewertung' => $oResult->kBewertung],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * anonymize received payments.
     * (replace `cZahler`(e-mail) in `tzahlungseingang`)
     */
    private function anon_tzahlungseingang()
    {
        $this->szReason = $this->szReasonName . 'anonymize outdated payments';
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tzahlungseingang
            WHERE
                cZahler != "-"
                AND cAbgeholt != "N"
                AND kBestellung IN (
                    SELECT kBestellung
                    FROM tbestellung b
                    WHERE b.kKunde NOT IN (SELECT kKunde FROM tkunde)
                )
                AND DATE(dZeit) < DATE(DATE_SUB(:pNow, INTERVAL :pInterval DAY))
                LIMIT :pLimit',
            [
                'pInterval' => $this->iInterval,
                'pNow'      => $this->oNow->format('Y-m-d H:i:s'),
                'pLimit'    => $this->iWorkLimit
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $this->oLogger->debug('vResult sets: '.print_r(\count($vResult) ,true )); // --DEBUG--
        if (!\is_array($vResult)) {

            return;
        }
        foreach ((array)$vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('UPDATE tzahlungseingang
                SET
                    cZahler = "-"
                WHERE
                    kZahlungseingang = :pKeyZahlungseingang',
                ['pKeyZahlungseingang' => $oResult->kZahlungseingang],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * anonymize comments of news, where no (more) registered customers are there for these.
     * (delete names and e-mails from `tnewskommentar` and remove the customer-relation)
     *
     * CONSIDERE: using no time-base or limit!
     */
    private function anon_tnewskommentar()
    {
        $this->szReason .= 'anonymize orphanded news-comments';
        $vResult         = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tnewskommentar
            WHERE
                cName != "Anonym"
                AND cEmail != "Anonym"
                AND kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)
                LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('UPDATE tnewskommentar
                SET
                    cName  = "Anonym",
                    cEmail = "Anonym",
                    kKunde = 0
                WHERE
                    kNewsKommentar = :pKeyNewsKommentar',
                ['pKeyNewsKommentar' => $oResult->kNewsKommentar],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

