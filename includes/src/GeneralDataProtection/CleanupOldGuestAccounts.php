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
    protected $szReason = 'cleanup_old_geust_accounts';

    public function execute()
    {
        $this->clean_tkunde();
    }

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
            'nLoginversuche' => null,
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFileds = $this->selectFields($vTableFields);
        // select all the data from the DB
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM `tkunde` e
            WHERE
                `nRegistriert` = 0
                AND `cAbgeholt` = "Y"
                AND `dErstellt` <= NOW() - INTERVAL :pInterval DAY',
            ['pInterval' => $this->iInterval],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!is_array($vResult)) {
            // "no data, no operation"
            return;
        }
        // save parts of the old values in the changes-journal..
        $this->saveToJournal('tkunde', $vUseFileds, $vResult);
        // anonymize the original data
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM `tkunde`
                WHERE kKunde = pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}
