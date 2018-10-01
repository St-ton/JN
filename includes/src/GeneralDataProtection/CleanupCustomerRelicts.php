<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * clean up multiple tables at each run
 * (normaly one times a day)
 *
 * names of the tables, we manipulate:
 *
 * `tbesucher`
 * `tbesucherarchiv`
 * `tkundenattribut`
 * `tkundenkontodaten`
 * `tkundenwerbenkunden`
 * `tkundenwerbenkundenbonus`
 * `tzahlungsinfo`
 * `tlieferadresse`
 * `trechnungsadresse`
 *
 * data will be removed here!
 */
class CleanupCustomerRelicts extends Method implements MethodInterface
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
        $this->szReason = __CLASS__.'clean_customer_relicts';
    }

    /**
     * runs all anonymize-routines
     */
    public function execute()
    {
        $this->del_tbesucher();
        $this->del_tbesucherarchiv();
        $this->del_tkundenattribut();
        $this->del_tkundenkontodaten();
        $this->del_tkundenwerbenkunden();
        $this->del_tzahlungsinfo();
        $this->del_tlieferadresse();
        $this->del_trechnungsadresse();
    }

    /**
     * delete visitors without a valid customer-account,
     * after a given count of days
     */
    private function del_tbesucher()
    {
        $vTableFields = [
            'kBesucher'         => null,
            'cIP'               => null,
            'cSessID'           => null,
            'cID'               => null,
            'kKunde'            => 1,
            'kBestellung'       => 1,
            'cReferer'          => 1,
            'cUserAgent'        => 1,
            'cEinstiegsseite'   => null,
            'cBrowser'          => 1,
            'cAusstiegsseite'   => null,
            'kBesucherBot'      => null,
            'dLetzteAktivitaet' => null,
            'dZeit'             => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tbesucher
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tbesucher', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tbesucher
                WHERE
                    kBesucher = :pKeyBesucher',
                ['pKeyBesucher' => $oResult->kBesucher],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete visitors in the visitors-archive immediately (at each run of the cron),
     * without a valid customer-account
     */
    private function del_tbesucherarchiv()
    {
        $vTableFields = [
            'kBesucher'       => null,
            'cIP'             => null,
            'kKunde'          => 1,
            'kBestellung'     => 1,
            'cReferer'        => null,
            'cEinstiegsseite' => null,
            'cBrowser'        => 1,
            'cAusstiegsseite' => null,
            'nBesuchsdauer'   => null,
            'kBesucherBot'    => null,
            'dZeit'           => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tbesucherarchiv
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tbesucherarchiv', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tbesucherarchiv
                WHERE
                    kBesucher = :pKeyBesucher',
                ['pKeyBesucher' => $oResult->kBesucher],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

    }

    /**
     * delete customer-recruitings
     * where no valid customer-accounts exists
     */
    private function del_tkundenwerbenkunden()
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
            'dErstellt'           => 1,
            '_bonus_fGuthaben'    => 1,
            '_bonus_nBonuspunkte' => 1,
            '_bonus_dErhalten'    => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT
                k.*,
                b.fGuthaben AS "_bonus_fGuthaben",
                b.nBonuspunkte AS "_bonus_nBonuspunkte",
                b.dErhalten AS "_bonus_dErhalten"
            FROM
                tkundenwerbenkunden k
                    LEFT JOIN tkundenwerbenkundenbonus b ON k.kKunde = b.kKunde
            WHERE
                k.kKunde > 0
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tkundenwerbenkunden,tkundenwerbenkundenbonus', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            // delete each "kKunde", in multiple tables, in one shot
            \Shop::Container()->getDB()->queryPrepared('DELETE tkundenwerbenkunden, tkundenwerbenkundenbonus
                FROM
                    tkundenwerbenkunden
                    LEFT JOIN tkundenwerbenkundenbonus ON tkundenwerbenkundenbonus.kKunde = tkundenwerbenkunden.kKunde
                WHERE
                    tkundenwerbenkunden.kKunde = :pKeyKunde',
                ['pKeyKunde' => $oResult->kKunde],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }

    }

    /**
     * delete customer-attributes
     * for which there are no valid customer-accounts
     */
    private function del_tkundenattribut()
    {
        $vTableFields = [
            'kKundenAttribut' => null,
            'kKunde'          => 1,
            'kKundenfeld'     => 1,
            'cName'           => 1,
            'cWert'           => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkundenattribut
            WHERE
                kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tkundenattribut', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkundenattribut
                WHERE
                    kKundenAttribut = :pKeykKundenAttribut',
                ['pKeykKundenAttribut' => $oResult->kKundenAttribut],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete payment-data of customers,
     * which have no valid account
     */
    private function del_tzahlungsinfo()
    {
        $vTableFields = [
            'kZahlungsInfo'     => null,
            'kBestellung'       => 1,
            'kKunde'            => 1,
            'cBankName'         => null,
            'cBLZ'              => null,
            'cKontoNr'          => null,
            'cIBAN'             => 1,
            'cBIC'              => null,
            'cKartenNr'         => 1,
            'cGueltigkeit'      => 1,
            'cCVV'              => 1,
            'cKartenTyp'        => 1,
            'cInhaber'          => 1,
            'cVerwendungszweck' => null,
            'cAbgeholt'         => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tzahlungsinfo
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tzahlungsinfo', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tzahlungsinfo
                WHERE
                    kZahlungsInfo = :pKeyZahlungsInfo',
                ['pKeyZahlungsInfo' => $oResult->kZahlungsInfo],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete bank-account-information of customers,
     * which have no valid account
     */
    private function del_tkundenkontodaten()
    {
        $vTableFields = [
            'kKundenKontodaten' => null,
            'kKunde'            => 1,
            'cBLZ'              => null,
            'nKonto'            => null,
            'cInhaber'          => 1,
            'cBankName'         => 1,
            'cIBAN'             => 1,
            'cBIC'              => null
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tkundenkontodaten
            WHERE
                kKunde > 0
                AND kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tkundenkontodaten', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tkundenkontodaten
                WHERE
                    kKundenKontodaten = :pKeyKundenKontodaten',
                ['pKeyKundenKontodaten' => $oResult->kKundenKontodaten],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete delivery-addresses
     * which assigned to no valid customer-account
     */
    private function del_tlieferadresse()
    {
        $vTableFields = [
            'kLieferadresse' => null,
            'kKunde'         => 1,
            'cAnrede'        => null,
            'cVorname'       => 1,
            'cNachname'      => 1,
            'cTitel'         => null,
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
            'cMobil'         => 1,
            'cFax'           => null,
            'cMail'          => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM tlieferadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
            WHERE
                b.cStatus IN (4, -1)
                AND b.cAbgeholt = "Y"
                AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('tlieferadresse', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM tlieferadresse
                WHERE
                    kLieferadresse = :pKeyLieferadresse',
                ['pKeyLieferadresse' => $oResult->kLieferadresse],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

    /**
     * delete billing-addresses
     * which assigned to no valid customer-account
     */
    private function del_trechnungsadresse()
    {
        $vTableFields = [
            'kRechnungsadresse' => null,
            'kKunde'            => 1,
            'cAnrede'           => null,
            'cTitel'            => null,
            'cVorname'          => 1,
            'cNachname'         => 1,
            'cFirma'            => 1,
            'cZusatz'           => null,
            'cStrasse'          => 1,
            'cHausnummer'       => 1,
            'cAdressZusatz'     => null,
            'cPLZ'              => 1,
            'cOrt'              => 1,
            'cBundesland'       => null,
            'cLand'             => 1,
            'cTel'              => 1,
            'cMobil'            => 1,
            'cFax'              => null,
            'cUSTID'            => null,
            'cWWW'              => null,
            'cMail'             => 1
        ];

        // don't customize below this line - - - - - - - - - - - - - - - - - - - -

        $vUseFields = $this->selectFields($vTableFields);
        $vResult = \Shop::Container()->getDB()->queryPrepared('SELECT *
            FROM trechnungsadresse k
                JOIN tbestellung b ON b.kKunde = k.kKunde
                WHERE
                    b.cStatus IN (4, -1)
                    AND b.cAbgeholt = "Y"
                    AND k.kKunde NOT IN (SELECT kKunde FROM tkunde)',
            [],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        if (!\is_array($vResult)) {

            return;
        }
        $this->saveToJournal('trechnungsadresse', $vUseFields, $vResult);
        foreach ($vResult as $oResult) {
            \Shop::Container()->getDB()->queryPrepared('DELETE FROM trechnungsadresse
                WHERE
                    kRechnungsadresse = :pKeyRechnungsadresse',
                ['pKeyRechnungsadresse' => $oResult->kRechnungsadresse],
                \DB\ReturnType::AFFECTED_ROWS
            );
        }
    }

}

