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
     * AnonymizeDeletedCustomer constructor
     *
     * @param $oNow
     * @param $iInterval
     */
    public function __construct($oNow, $iInterval)
    {
        parent::__construct($oNow, $iInterval);
        $this->szReason = __CLASS__.': anonymize_deleted_customer';
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
        $vTableFields = [
            'kBewertung'      => 1,
            'kArtikel'        => 1,
            'kKunde'          => 1,
            'kSprache'        => null,
            'cName'           => null,
            'cTitel'          => 1,
            'cText'           => null,
            'nHilfreich'      => null,
            'nNichtHilfreich' => null,
            'nSterne'         => 1,
            'nAktiv'          => null,
            'dDatum'          => 1,
            'cAntwort'        => null,
            'dAntwortDatum'   => null,
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tbewertung b
            WHERE
                b.cName != "Anonym"
                AND b.kKunde > 0
                AND b.kKunde NOT IN (SELECT kKunde FROM tkunde)
                AND date(dDatum) < date_sub(date(now()), INTERVAL :pInterval DAY)',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tbewertung', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('UPDATE tbewertung b
                SET
                    b.cName = "Anonym",
                    kKunde  = 0
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
        $vTableFields = [
            'kZahlungseingang'  => null,
            'kBestellung'       => 1,
            'cZahlungsanbieter' => 1,
            'fBetrag'           => null,
            'fZahlungsgebuehr'  => null,
            'cISO'              => null,
            'cEmpfaenger'       => 1,
            'cZahler'           => 1,
            'dZeit'             => 1,
            'cHinweis'          => null,
            'cAbgeholt'         => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tzahlungseingang
            WHERE
                cZahler != "-"
                AND cAbgeholt != "N"
                AND kBestellung IN (
                    SELECT kBestellung
                    FROM tbestellung b
                    WHERE b.kKunde NOT IN (SELECT kKunde FROM tkunde)
                )
                AND date(dZeit) < date(date_sub(now(), INTERVAL :pInterval DAY))',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tzahlungseingang', $vUseFields, $vResult);
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
     * (delete names an e-mails from `tnewskommentar` and remove the customer-relation)
     */
    private function anon_tnewskommentar()
    {
        $vTableFields = [
            'kNewsKommentar' => null,
            'kNews'          => null,
            'kKunde'         => 1,
            'nAktiv'         => null,
            'cName'          => 1,
            'cEmail'         => 1,
            'cKommentar'     => null,
            'dErstellt'      => 1,
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tnewskommentar
            WHERE
                cName != "Anonym"
                AND kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tnewskommentar', $vUseFields, $vResult);
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

