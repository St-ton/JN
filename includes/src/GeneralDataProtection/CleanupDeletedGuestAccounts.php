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
class CleanupDeletedGuestAccounts extends Method implements MethodInterface
{
    protected $szReason = 'cleanup_deleted_guest_accounts';

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

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkunde` k
                JOIN `tbestellung` b ON b.`kKunde` = k.`kKunde`
            WHERE
                b.`cStatus` IN (4, -1)
                AND k.`nRegistriert` = 0
                AND b.`cAbgeholt` = "Y"',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tbesucher', $vUseFileds, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tkunde`
                WHERE
                    kKunde = pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}
