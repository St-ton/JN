<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * Deleted guest accounts with no open orders
 *
 * names of the tables, we manipulate:
 *
 * `tkunde`
 */
class CleanupGuestAccountsWithoutOrders extends Method implements MethodInterface
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
        $this->cleanup_tkunde();
    }

    /**
     * delete not registered customers (relicts)
     */
    private function cleanup_tkunde()
    {
        $vTableFields = [
            'kKunde'         => 1,
            'kKundengruppe'  => null,
            'kSprache'       => null,
            'cKundenNr'      => 1,
            'cPasswort'      => null,
            'cAnrede'        => null,
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
            'cTel'           => 1,
            'cMobil'         => null,
            'cFax'           => null,
            'cMail'          => 1,
            'cUSTID'         => 1,
            'cWWW'           => null,
            'cSperre'        => null,
            'fGuthaben'      => null,
            'cNewsletter'    => null,
            'dGeburtstag'    => null,
            'fRabatt'        => null,
            'cHerkunft'      => null,
            'dErstellt'      => 1,
            'dVeraendert'    => null,
            'cAktiv'         => null,
            'cAbgeholt'      => null,
            'nRegistriert'   => 1,
            'nLoginversuche' => null
        ];
        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $this->szReason = $this->szReasonName . 'delete not registered customers';
        $vUseFields     = $this->selectFields($vTableFields);
        $vResult        = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkunde k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cStatus IN (' . BESTELLUNG_STATUS_VERSANDT . ', ' . BESTELLUNG_STATUS_STORNO . ')
                AND k.nRegistriert = 0
                AND b.cAbgeholt = "Y"
            LIMIT :pLimit',
            ['pLimit' => $this->iWorkLimit],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tbestellung', $vUseFields, 'kKunde', $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkunde
                WHERE
                    kKunde = :pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

