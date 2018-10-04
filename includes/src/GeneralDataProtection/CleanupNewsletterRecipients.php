<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Delete newsletter-registrations with no opt-in within given interval
 * (interval former "interval_clear_logs" = 90 days)
 *
 * names of the tables, we manipulate:
 *
 * `tnewsletterempfaenger`
 * `tnewsletterempfaengerhistory`
 */
class CleanupNewsletterRecipients extends Method implements MethodInterface
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
        $this->clean_tnewsletter();
    }

    /**
     * delete newsletter-registrations withi no "opt-in"
     * within the given interval
     */
    private function clean_tnewsletter()
    {
        $vTableFields = [
            // `tnewsletterempfaenger`
            'kNewsletterEmpfaenger'        => null,
            'kSprache'                     => null,
            'kKunde'                       => 1,
            'nAktiv'                       => null,
            'cAnrede'                      => null,
            'cVorname'                     => 1,
            'cNachname'                    => 1,
            'cEmail'                       => 1,
            'cOptCode'                     => null,
            'cLoeschCode'                  => null,
            'dEingetragen'                 => 1,
            'dLetzterNewsletter'           => null,
            // `tnewsletterempfaengerhistory`
            'kNewsletterEmpfaengerHistory' => null,
            'kSprache'                     => null,
            'kKunde'                       => 1,
            'cAnrede'                      => null,
            'cVorname'                     => null,
            'cNachname'                    => null,
            'cEmail'                       => 1,
            'cOptCode'                     => null,
            'cLoeschCode'                  => null,
            'cAktion'                      => null,
            'cEmailBodyHtml'               => null,
            'cRegIp'                       => 1,
            'cOptIp'                       => null,
            'dAusgetragen'                 => 1,
            'dEingetragen'                 => null,
            'dOptCode'                     => null
        ];
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $this->szReason = $this->szReasonName . 'delete unconfirmed newsletter-registrations';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tnewsletterempfaenger e
                JOIN tnewsletterempfaengerhistory h ON h.cOptCode = e.cOptCode AND h.cEmail = e.cEmail
            WHERE
                e.nAktiv = 0
                AND h.cAktion = "Eingetragen"
                AND (h.dOptCode = "0000-00-00 00:00:00" OR h.dOptCode IS NULL)
                AND h.dEingetragen <= (:pNow - INTERVAL :pInterval DAY)
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
        $this->saveToJournal('tnewsletterempfaenger', $vUseFields, 'kKunde', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE e, h
                FROM tnewsletterempfaenger e
                   INNER JOIN tnewsletterempfaengerhistory h ON h.cOptCode = e.cOptCode AND h.cEmail = e.cEmail
                WHERE
                   e.cOptCode = :pOpCode',
                ['pOpCode' => $oResult->cOptCode],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

