<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Remove guest accounts fetched by JTL-Wawi and older than x days
 * (interval former "interval_delete_guest_accounts" = 365 days)
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupOldGuestAccounts extends Method implements MethodInterface
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
        $this->clean_tkunde();
    }

    /**
     * delete old guest accounts
     */
    private function clean_tkunde()
    {
        $vTableFields = [
            'kKunde'         => 1,
            'kKundengruppe'  => null,
            'kSprache'       => null,
            'cKundenNr'      => 1,
            'cPasswort'      => null,
            'cAnrede'        => 1,
            'cTitel'         => null,
            'cVorname'       => 1,
            'cNachname'      => 1,
            'cFirma'         => 1,
            'cZusatz'        => null,
            'cStrasse'       => 1,
            'cHausnummer'    => 1,
            'cAdressZusatz'  => null,
            'cPLZ'           => 1,
            'cOrt'           => 1,
            'cBundesland'    => null,
            'cLand'          => 1,
            'cTel'           => null,
            'cMobil'         => null,
            'cFax'           => null,
            'cMail'          => 1,
            'cUSTID'         => 1,
            'cWWW'           => null,
            'cSperre'        => null,
            'fGuthaben'      => null,
            'cNewsletter'    => null,
            'dGeburtstag'    => 1,
            'fRabatt'        => null,
            'cHerkunft'      => null,
            'dErstellt'      => 1,
            'dVeraendert'    => 1,
            'cAktiv'         => 1,
            'cAbgeholt'      => null,
            'nRegistriert'   => null,
            'nLoginversuche' => null
        ];
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $this->szReason = $this->szReasonName . 'delete customer-data-historytory';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkunde e
            WHERE
                nRegistriert = 0
                AND cAbgeholt = "Y"
                AND dErstellt <= :pNow - INTERVAL :pInterval DAY
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
        $this->saveToJournal('tkunde', $vUseFields, 'kKunde', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkunde
                WHERE kKunde = pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

