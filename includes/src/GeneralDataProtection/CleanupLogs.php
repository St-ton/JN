<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Delete old logs containing personal data.
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `temailhistory`
 * `tkontakthistory`
 * `tkundenwerbenkunden`
 * `tzahlungslog`
 * `tproduktanfragehistory`
 * `tverfuegbarkeitsbenachrichtigung`
 * `tjtllog`
 * `tzahlungseingang`
 * `tkundendatenhistory`
 */
class CleanupLogs extends Method implements MethodInterface
{
    /**
     * @var string
     */
    protected $szReason = 'cleanup_logs';

    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->clean_temailhistory();
        $this->clean_tkontakthistory();
        $this->clean_tkundenwerbenkunden();
        $this->clean_tzahlungslog();
        $this->clean_tproduktanfragehistory();
        $this->clean_tverfuegbarkeitsbenachrichtigung();
        $this->clean_tjtllog();
        $this->clean_tzahlungseingang();
        $this->clean_tkundendatenhistory();
    }

    /**
     * delete email-history
     * older than given interval
     */
    private function clean_temailhistory()
    {
        $vTableFields = [
            'kEmailhistory' => null,
            'kEmailvorlage' => null,
            'cSubject'      => 1,
            'cFromName'     => 1,
            'cFromEmail'    => 1,
            'cToName'       => 1,
            'cToEmail'      => 1,
            'dSent'         => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `temailhistory` e
            WHERE `dSent` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('temailhistory', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            // $vQueries[] = "DELETE FROM temailhistory WHERE dSent <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM temailhistory
                WHERE kEmailhistory = :pKeyEmailhistory',
                ['pKeyEmailhistory' => $oResult->kEmailhistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-hostory
     * older than given interval
     */
    private function clean_tkontakthistory()
    {
        $vTableFields = [
            'kKontaktHistory' => null,
            'kKontaktBetreff' => 1,
            'kSprache'        => null,
            'cAnrede'         => null,
            'cVorname'        => 1,
            'cNachname'       => 1,
            'cFirma'          => 1,
            'cTel'            => null,
            'cMobil'          => null,
            'cFax'            => null,
            'cMail'           => 1,
            'cNachricht'      => null,
            'cIP'             => null,
            'dErstellt'       => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkontakthistory` e
            WHERE `dErstellt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tkontakthistory', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            // $vQueries[] = "DELETE FROM tkontakthistory WHERE dErstellt <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tkontakthistory`
                WHERE kKontaktHistory = :pKeyKontaktHistory',
                ['pKeyKontaktHistory' => $oResult->kKontaktHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-recruitings
     * older than the given interval
     */
    private function clean_tkundenwerbenkunden()
    {
        $vTableFields = [
            'kKundenWerbenKunden' => null,
            'kKunde'              => 1,
            'cVorname'            => 1,
            'cNachname'           => 1,
            'cEmail'              => 1,
            'nRegistriert'        => null,
            'nGuthabenVergeben'   => null,
            'fGuthaben'           => null,
            'dErstellt'           => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkundenwerbenkunden` e
            WHERE `dErstellt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tkundenwerbenkunden', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tkundenwerbenkunden WHERE dErstellt <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE `tkundenwerbenkunden`
                WHERE kKundenWerbenKunden = :pKeyKundenWerbenKunden',
                ['pKeyKundenWerbenKunden' => $oResult->kKundenWerbenKunden],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete log-entries of payments
     * older than the given interval
     */
    private function clean_tzahlungslog()
    {
        $vTableFields = [
            'kZahlunglog' => null,
            'cModulId'    => 1,
            'cLog'        => null,
            'cLogData'    => 1,
            'nLevel'      => null,
            'dDatum'      => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tzahlungslog` e
            WHERE `dDatum` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tzahlungslog', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tzahlungslog WHERE dDatum <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tzahlungslog`
                WHERE kZahlunglog = :pKeyZahlunglog',
                ['pKeyZahlunglog' => $oResult->kZahlunglog],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete product demans of customers,
     * older than the given interval
     */
    private function clean_tproduktanfragehistory()
    {
        $vTableFields = [
            'kProduktanfrageHistory' => null,
            'kArtikel'               => 1,
            'kSprache'               => null,
            'cAnrede'                => null,
            'cVorname'               => 1,
            'cNachname'              => 1,
            'cFirma'                 => 1,
            'cTel'                   => null,
            'cMobil'                 => null,
            'cFax'                   => null,
            'cMail'                  => 1,
            'cNachricht'             => null,
            'cIP'                    => null,
            'dErstellt'              => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tproduktanfragehistory` e
            WHERE `dErstellt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tproduktanfragehistory', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tproduktanfragehistory WHERE dErstellt <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tproduktanfragehistory`
                WHERE kProduktanfrageHistory = :pKeyProduktanfrageHistory',
                ['pKeyProduktanfrageHistory' => $oResult->kProduktanfrageHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete availability demans of customers,
     * older than the given interval
     */
    private function clean_tverfuegbarkeitsbenachrichtigung()
    {
        $vTableFields = [
            'kVerfuegbarkeitsbenachrichtigung' => null,
            'kArtikel'                         => null,
            'kSprache'                         => null,
            'cVorname'                         => 1,
            'cNachname'                        => 1,
            'cMail'                            => 1,
            'cIP'                              => null,
            'cAbgeholt'                        => null,
            'nStatus'                          => null,
            'dErstellt'                        => 1,
            'dBenachrichtigtAm'                => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tverfuegbarkeitsbenachrichtigung` e
            WHERE `dErstellt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tverfuegbarkeitsbenachrichtigung', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tverfuegbarkeitsbenachrichtigung WHERE dBenachrichtigtAm <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tverfuegbarkeitsbenachrichtigung`
                WHERE kVerfuegbarkeitsbenachrichtigung = :pKeyVerfuegbarkeitsbenachrichtigung',
                ['pKeyVerfuegbarkeitsbenachrichtigung' => $oResult->kVerfuegbarkeitsbenachrichtigung],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete jtl-log-entries,
     * older than the given interval
     */
    private function clean_tjtllog()
    {
        $vTableFields = [
            'kLog'      => null,
            'nLevel'    => null,
            'cLog'      => 1,
            'cKey'      => null,
            'kKey'      => null,
            'dErstellt' => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tjtllog` e
            WHERE `dErstellt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        //$this->saveToJournal('tjtllog', $vUseFields, $vResult); // --TO-CHECK-- did we need a journal really?
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tjtllog WHERE (cLog LIKE '%@%' OR cLog LIKE '%kKunde%') AND dErstellt <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tjtllog`
                WHERE kLog = :pKeyLog',
                ['pKeyLog' => $oResult->kLog],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete payment-confirmations of customers,
     * older than the given interval
     */
    private function clean_tzahlungseingang()
    {
        $vTableFields =[
            'kZahlungseingang'  => null,
            'kBestellung'       => 1,
            'cZahlungsanbieter' => 1,
            'fBetrag'           => 1,
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
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tzahlungseingang` e
            WHERE `cAbgeholt` <= (NOW() - INTERVAL ' . $this->iInterval . ' DAY)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tzahlungseingang', $vUseFields, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            //$vQueries[] = "DELETE FROM tzahlungseingang WHERE cAbgeholt != 'N' AND dZeit <= NOW() - INTERVAL " . $this->iInterval . " DAY";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tzahlungseingang`
                WHERE kZahlungseingang = :pKeyZahlungseingang',
                ['pKeyZahlungseingang' => $oResult->kZahlungseingang],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-data-historytory
     */
    private function clean_tkundendatenhistory()
    {
        $vTableFields = [
            'kKundendatenHistory' => null,
            'kKunde'              => null,
            'cJsonAlt'            => null,
            'cJsonNeu'            => null,
            'cQuelle'             => null,
            'dErstellt'           => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkundendatenhistory`
            WHERE `dErstellt` <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        //$this->saveToJournal('tkundendatenhistory', $vUseFields, $vResult); // --TO-CHECK-- did we need a journal here really?
        // anonymize the original data
        foreach ($vResult as $oResult) {
            // Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu)
            //$vQueries[] = "DELETE FROM tkundendatenhistory WHERE dErstellt <= LAST_DAY(DATE_ADD(NOW() - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(NOW()) MONTH))";

            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tkundendatenhistory`
                WHERE kKundendatenHistory = :pKeyKundendatenHistory',
                ['pKeyKundendatenHistory' => $oResult->kKundendatenHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}
