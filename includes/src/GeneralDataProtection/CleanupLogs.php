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
        $this->clean_temailhistory();                    // no protocolling
        $this->clean_tkontakthistory();                  // no protocolling
        $this->clean_tkundenwerbenkunden();
        $this->clean_tzahlungslog();
        $this->clean_tproduktanfragehistory();           // no protocolling
        $this->clean_tverfuegbarkeitsbenachrichtigung();
        $this->clean_tjtllog();                          // no protocolling
        $this->clean_tzahlungseingang();
        $this->clean_tkundendatenhistory();              // no protocolling
    }

    /**
     * delete email-history
     * older than given interval
     */
    private function clean_temailhistory()
    {
        /*
         *$vTableFields = [
         *    'kEmailhistory' => null,
         *    'kEmailvorlage' => null,
         *    'cSubject'      => 1,
         *    'cFromName'     => 1,
         *    'cFromEmail'    => 1,
         *    'cToName'       => 1,
         *    'cToEmail'      => 1,
         *    'dSent'         => null
         *];
         */
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete email-history';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM temailhistory e
            WHERE dSent <= (:pNow - INTERVAL :pInterval DAY)
            LIMIT :pLimit',
            [
                'pInterval' => $this->iInterval,
                'pLimit'    => $this->iWorkLimit,
                'pNow'      => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        //$this->saveToJournal('temailhistory', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
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
        /*
         *$vTableFields = [
         *    'kKontaktHistory' => null,
         *    'kKontaktBetreff' => 1,
         *    'kSprache'        => null,
         *    'cAnrede'         => null,
         *    'cVorname'        => 1,
         *    'cNachname'       => 1,
         *    'cFirma'          => 1,
         *    'cTel'            => null,
         *    'cMobil'          => null,
         *    'cFax'            => null,
         *    'cMail'           => 1,
         *    'cNachricht'      => null,
         *    'cIP'             => null,
         *    'dErstellt'       => 1
         *];
         */

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete customer-hostory';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkontakthistory e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
        //$this->saveToJournal('tkontakthistory', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkontakthistory
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

        $this->szReason = $this->szReasonName . 'delete customer-recruitings';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkundenwerbenkunden e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
        $this->saveToJournal('tkundenwerbenkunden', $vUseFields, 'kKunde', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE tkundenwerbenkunden
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
        /*
         *$vTableFields = [
         *    'kZahlunglog' => null,
         *    'cModulId'    => 1,
         *    'cLog'        => null,
         *    'cLogData'    => 1,
         *    'nLevel'      => null,
         *    'dDatum'      => 1
         *];
         */
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete log-entries of payments';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tzahlungslog e
            WHERE dDatum <= (:pNow - INTERVAL :pInterval DAY)
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
        //$this->saveToJournal('tzahlungslog', $vUseFields, 'kZahlunglog', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tzahlungslog
                WHERE kZahlunglog = :pKeyZahlunglog',
                ['pKeyZahlunglog' => $oResult->kZahlunglog],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete product demands of customers,
     * older than the given interval
     */
    private function clean_tproduktanfragehistory()
    {
        /*
         *$vTableFields = [
         *    'kProduktanfrageHistory' => null,
         *    'kArtikel'               => 1,
         *    'kSprache'               => null,
         *    'cAnrede'                => null,
         *    'cVorname'               => 1,
         *    'cNachname'              => 1,
         *    'cFirma'                 => 1,
         *    'cTel'                   => null,
         *    'cMobil'                 => null,
         *    'cFax'                   => null,
         *    'cMail'                  => 1,
         *    'cNachricht'             => null,
         *    'cIP'                    => null,
         *    'dErstellt'              => 1
         *];
         */

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete product demands';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tproduktanfragehistory e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
        //$this->saveToJournal('tproduktanfragehistory', $vUseFields, 'kArtikel', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tproduktanfragehistory
                WHERE kProduktanfrageHistory = :pKeyProduktanfrageHistory',
                ['pKeyProduktanfrageHistory' => $oResult->kProduktanfrageHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete availability demands of customers,
     * older than the given interval
     */
    private function clean_tverfuegbarkeitsbenachrichtigung()
    {
        $vTableFields = [
            'kVerfuegbarkeitsbenachrichtigung' => null,
            'kArtikel'                         => 1,
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

        $this->szReason = $this->szReasonName . 'delete availability demands';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tverfuegbarkeitsbenachrichtigung e
            WHERE dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
        $this->saveToJournal('tverfuegbarkeitsbenachrichtigung', $vUseFields, 'kArtikel', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tverfuegbarkeitsbenachrichtigung
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
        /*
         *$vTableFields = [
         *    'kLog'      => null,
         *    'nLevel'    => null,
         *    'cLog'      => 1,
         *    'cKey'      => null,
         *    'kKey'      => 1,
         *    'dErstellt' => 1
         *];
         */
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete jtl-log-entries';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tjtllog
            WHERE
                (cLog LIKE "%@%" OR cLog LIKE "%kKunde%")
                AND dErstellt <= (:pNow - INTERVAL :pInterval DAY)
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
        //$this->saveToJournal('tjtllog', $vUseFields, 'kKey', $vResult); 
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tjtllog
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

        $this->szReason = $this->szReasonName . 'delete payment-confirmations';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tzahlungseingang
            WHERE
                cAbgeholt != "N"
                AND dZeit <= (:pNow - INTERVAL :pInterval DAY)
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
        $this->saveToJournal('tzahlungseingang', $vUseFields, 'kBestellung', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tzahlungseingang
                WHERE kZahlungseingang = :pKeyZahlungseingang',
                ['pKeyZahlungseingang' => $oResult->kZahlungseingang],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete customer-data-historytory
     *
     * CONSIDER: using no time-base or limit here!
     */
    private function clean_tkundendatenhistory()
    {
        //$vTableFields = [
            //'kKundendatenHistory' => null,
            //'kKunde'              => null,
            //'cJsonAlt'            => null,
            //'cJsonNeu'            => null,
            //'cQuelle'             => null,
            //'dErstellt'           => null
        //];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        //$this->szReason = $this->szReasonName . 'delete customer-data-historytory';
        //$vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkundendatenhistory
            WHERE dErstellt <= LAST_DAY(DATE_ADD(:pNow1 - INTERVAL 2 YEAR, INTERVAL 12 - MONTH(:pNow2) MONTH))',
            [
                'pNow1' => $this->oNow->format('Y-m-d H:i:s'),
                'pNow2' => $this->oNow->format('Y-m-d H:i:s')
            ],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        //$this->saveToJournal('tkundendatenhistory', $vUseFields, 'kKunde', $vResult);
        foreach ($vResult as $oResult) {
            // Customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu)
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkundendatenhistory
                WHERE kKundendatenHistory = :pKeyKundendatenHistory',
                ['pKeyKundendatenHistory' => $oResult->kKundendatenHistory],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

